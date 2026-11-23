<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\MovieCast;
use App\Models\MovieTorrent;
use App\Models\Post;
use App\Models\User;
use App\Services\TmdbService;
use App\Services\Torrent\Providers\GenericWordPressProvider;
use App\Services\Torrent\TorrentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

/**
 * Controller para executar comandos Artisan via web na área ADM.
 *
 * Apenas admins podem acessar. Os comandos são predefinidos para evitar execução arbitrária.
 */
class AdmConsoleController extends Controller
{
    /**
     * Comandos predefinidos permitidos.
     */
    protected array $allowedCommands = [
        'migrate' => [
            'name' => 'Rodar migrações',
            'description' => 'Executa as migrações de banco de dados pendentes.',
            'command' => 'migrate',
            'params' => ['--force' => true],
            'dangerous' => false,
            'ajax' => false,
        ],
        'migrate:rollback' => [
            'name' => 'Reverter migração',
            'description' => 'Reverte a última migração executada.',
            'command' => 'migrate:rollback',
            'params' => ['--force' => true],
            'dangerous' => true,
            'ajax' => false,
        ],
        'db:seed-tmdb' => [
            'name' => 'Popular filmes TMDB',
            'description' => 'Busca filmes da TMDB API e salva no banco. Modo incremental adiciona apenas os novos.',
            'command' => 'db:seed-tmdb',
            'params' => ['--max' => 10000],
            'dangerous' => false,
            'ajax' => true,
        ],
        'db:seed-tmdb-reset' => [
            'name' => 'Resetar filmes TMDB',
            'description' => 'Apaga todos os filmes e recomeça o seed do zero. Use com cuidado!',
            'command' => 'db:seed-tmdb-reset',
            'params' => ['--max' => 10000],
            'dangerous' => true,
            'ajax' => true,
        ],
        'torrents:populate' => [
            'name' => 'Popular torrents',
            'description' => 'Busca torrents para todos os filmes e salva múltiplos links por qualidade.',
            'command' => 'torrents:populate',
            'params' => [],
            'dangerous' => false,
            'ajax' => true,
        ],
        'torrents:reset' => [
            'name' => 'Resetar torrents',
            'description' => 'Apaga todos os torrents salvos. Use antes de re-popular.',
            'command' => 'torrents:reset',
            'params' => [],
            'dangerous' => true,
            'ajax' => true,
        ],
        'db:populate-posters' => [
            'name' => 'Buscar capas dos filmes',
            'description' => 'Busca as capas (posters) na TMDB API para os filmes sem imagem.',
            'command' => 'db:populate-posters',
            'params' => [],
            'dangerous' => false,
            'ajax' => false,
        ],
        'cache:clear' => [
            'name' => 'Limpar cache',
            'description' => 'Limpa todo o cache da aplicação (config, routes, views).',
            'command' => 'cache:clear',
            'params' => [],
            'dangerous' => false,
            'ajax' => false,
        ],
        'storage:link' => [
            'name' => 'Criar link de storage',
            'description' => 'Cria o link simbólico entre storage/app/public e public/storage.',
            'command' => 'storage:link',
            'params' => [],
            'dangerous' => false,
            'ajax' => false,
        ],
        'db:seed-users' => [
            'name' => 'Criar usuários admin',
            'description' => 'Cria os 2 usuários administradores (Isaac e Root) com senha padrão.',
            'command' => 'db:seed',
            'params' => ['--class' => 'UsersTableSeeder', '--force' => true],
            'dangerous' => false,
            'ajax' => false,
        ],
        'db:dump' => [
            'name' => 'Exportar banco SQL',
            'description' => 'Gera um dump SQL do banco local para download.',
            'command' => 'db:dump',
            'params' => [],
            'dangerous' => false,
            'ajax' => false,
        ],
    ];

    /**
     * Exibe a lista de comandos disponíveis.
     */
    public function index(): View
    {
        $commands = $this->allowedCommands;

        return view('adm.console', [
            'commands' => $commands,
            'lastOutput' => session('console_output'),
            'lastExitCode' => session('console_exit_code'),
            'lastCommandName' => session('console_command_name'),
        ]);
    }

    /**
     * Executa um comando predefinido.
     */
    public function execute(Request $request): RedirectResponse
    {
        $commandKey = $request->input('command');

        // Valida que o comando está na lista permitida
        if (! isset($this->allowedCommands[$commandKey])) {
            return redirect()->route('adm.console')
                ->with('console_output', "Comando inválido: {$commandKey}")
                ->with('console_exit_code', 1)
                ->with('console_command_name', $commandKey);
        }

        $commandConfig = $this->allowedCommands[$commandKey];
        $command = $commandConfig['command'];
        $params = $commandConfig['params'];

        // Executa o comando — no Laravel 11, call() retorna o exit code diretamente
        $exitCode = Artisan::call($command, $params);
        $output = Artisan::output();

        return redirect()->route('adm.console')
            ->with('console_output', trim($output))
            ->with('console_exit_code', $exitCode)
            ->with('console_command_name', $commandConfig['name']);
    }

    /**
     * Inicia o seed TMDB por lotes via AJAX.
     *
     * Se incremental=true, conserva os filmes existentes e adiciona apenas os novos.
     * Se incremental=false (reset), limpa tudo e recomeça.
     * O navegador faz chamadas repetidas a seedTmdbBatch() até completar.
     */
    public function seedTmdb(Request $request): JsonResponse
    {
        $tmdb = new TmdbService();
        if (!$tmdb->isConfigured()) {
            return response()->json(['status' => 'error', 'message' => 'TMDB_API_KEY não configurado.'], 500);
        }

        $maxMovies = (int) ($request->input('max', 10000));
        $incremental = (bool) ($request->input('incremental', true));

        // Se não é incremental, limpa tudo
        if (!$incremental) {
            DB::table('movie_views')->delete();
            DB::table('movie_torrents')->delete();
            DB::table('movie_cast')->delete();
            DB::table('urls')->delete();
            DB::table('categorias_posts')->delete();
            DB::table('posts')->delete();
            DB::table('categorias')->delete();
        }

        // Cria ou recupera categorias
        $genreMapping = [
            28 => 'Ação', 16 => 'Animação', 35 => 'Comédia', 10749 => 'Comédia romântica',
            18 => 'Drama', 10752 => 'Documentário', 53 => 'Suspense', 80 => 'Filme policial',
            9648 => 'Suspense', 12 => 'Ficção científica', 14 => 'Ficção científica',
            36 => 'Filmes de guerra', 10402 => 'Musical', 10751 => 'Comédia romântica',
            878 => 'Ficção científica', 99 => 'Documentário', 10770 => 'Terror',
            27 => 'Terror', 37 => 'Faroeste (ou western)',
        ];
        $categoriaMap = [];
        foreach ($genreMapping as $tmdbGenreId => $categoriaName) {
            $categoria = Categoria::firstOrCreate(
                ['name' => $categoriaName],
                ['detalhes' => "Categoria importada da TMDB API.", 'status1' => 'ativo']
            );
            $categoriaMap[$tmdbGenreId] = $categoria->id;
        }
        $outrosCategoria = Categoria::firstOrCreate(
            ['name' => 'Outros'],
            ['detalhes' => 'Categoria Padrão, que vão os filmes sem categoria', 'status1' => 'ativo']
        );

        // Conta filmes existentes para o modo incremental
        $existingCount = 0;
        if ($incremental) {
            $existingCount = Post::whereNotNull('tmdb_id')->count();
        }

        // Busca total de filmes na TMDB
        $startYear = 2006;
        $endYear = 2026;
        $firstPage = $tmdb->getMoviesByReleaseDate("{$startYear}-01-01", "{$endYear}-12-31", 1);
        $totalResults = $firstPage['total_results'] ?? 0;

        // Salva o estado no arquivo de progresso
        $progressFile = storage_path('app/tmdb_seed_progress.json');
        file_put_contents($progressFile, json_encode([
            'status' => 'fetching',
            'message' => $incremental
                ? "Modo incremental: {$existingCount} filmes existentes. Buscando novos..."
                : "Reset: buscando {$maxMovies} filmes da TMDB...",
            'created' => 0,
            'skipped' => 0,
            'existing' => $existingCount,
            'max' => $maxMovies,
            'total_found' => $totalResults,
            'current_page' => 1,
            'incremental' => $incremental,
            'categoria_map' => $categoriaMap,
            'outros_categoria_id' => $outrosCategoria->id,
        ], JSON_UNESCAPED_UNICODE));

        return response()->json([
            'status' => 'initialized',
            'message' => $incremental
                ? "Seed incremental inicializado. {$existingCount} filmes existentes, {$totalResults} na TMDB."
                : "Reset inicializado. {$totalResults} filmes encontrados na TMDB.",
            'total_found' => $totalResults,
            'max' => $maxMovies,
            'existing' => $existingCount,
            'incremental' => $incremental,
        ]);
    }

    /**
     * Processa um lote de filmes da TMDB.
     *
     * Cada chamada processa uma página da TMDB (20 filmes por padrão).
     * No modo incremental, pula filmes que já existem pelo tmdb_id.
     * O navegador faz chamadas repetidas até completar o máximo desejado.
     */
    public function seedTmdbBatch(): JsonResponse
    {
        $progressFile = storage_path('app/tmdb_seed_progress.json');
        if (!file_exists($progressFile)) {
            return response()->json(['status' => 'error', 'message' => 'Seed não inicializado. Execute primeiro.']);
        }

        $state = json_decode(file_get_contents($progressFile), true);
        if (!$state) {
            return response()->json(['status' => 'error', 'message' => 'Estado inválido.']);
        }

        $tmdb = new TmdbService();
        $users = User::all();
        if ($users->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Nenhum usuário encontrado.']);
        }

        $currentPage = $state['current_page'] ?? 1;
        $created = $state['created'] ?? 0;
        $skipped = $state['skipped'] ?? 0;
        $existing = $state['existing'] ?? 0;
        $max = $state['max'] ?? 10000;
        $incremental = $state['incremental'] ?? true;
        $categoriaMap = $state['categoria_map'] ?? [];
        $outrosCategoryId = $state['outros_categoria_id'] ?? 1;

        // Busca a página atual da TMDB
        $pageResponse = $tmdb->getMoviesByReleaseDate('2006-01-01', '2026-12-31', $currentPage);
        $moviesList = $pageResponse['results'] ?? [];

        if (empty($moviesList)) {
            // Sem mais filmes — finaliza
            file_put_contents($progressFile, json_encode([
                'status' => 'done',
                'message' => "Seed concluído! {$created} novos filmes, {$skipped} pulados. Total: " . ($existing + $created) . " filmes.",
                'created' => $created,
                'skipped' => $skipped,
                'existing' => $existing,
                'max' => $max,
                'total_found' => $state['total_found'] ?? 0,
                'incremental' => $incremental,
            ], JSON_UNESCAPED_UNICODE));
            return response()->json([
                'status' => 'done',
                'message' => "Seed concluído! {$created} novos filmes, {$skipped} pulados. Total: " . ($existing + $created) . " filmes.",
                'created' => $created,
                'skipped' => $skipped,
                'existing' => $existing,
                'max' => $max,
            ]);
        }

        // Processa cada filme da página
        foreach ($moviesList as $movie) {
            if ($created >= $max) break;

            $releaseDate = $movie['release_date'] ?? null;
            if (empty($releaseDate)) {
                $skipped++;
                continue;
            }

            // No modo incremental, pula filmes que já existem
            $tmdbMovieId = $movie['id'] ?? null;
            if ($incremental && $tmdbMovieId !== null) {
                if (Post::where('tmdb_id', $tmdbMovieId)->exists()) {
                    $skipped++;
                    continue;
                }
            }

            try {
                $this->createMovieFromTmdb($movie, $categoriaMap, $outrosCategoryId, $users, $tmdb);
                $created++;
            } catch (\Exception $e) {
                $skipped++;
                report($e);
            }
        }

        $nextPage = $currentPage + 1;
        $isDone = ($created >= $max);

        // Atualiza o estado
        $stateData = [
            'status' => $isDone ? 'done' : 'fetching',
            'message' => $isDone
                ? "Seed concluído! {$created} novos filmes, {$skipped} pulados."
                : "Processando filmes... {$created}/{$max}",
            'created' => $created,
            'skipped' => $skipped,
            'existing' => $existing,
            'max' => $max,
            'total_found' => $state['total_found'] ?? 0,
            'current_page' => $nextPage,
            'incremental' => $incremental,
            'categoria_map' => $categoriaMap,
            'outros_categoria_id' => $outrosCategoryId,
        ];
        file_put_contents($progressFile, json_encode($stateData, JSON_UNESCAPED_UNICODE));

        if ($isDone) {
            @unlink($progressFile);
        }

        return response()->json([
            'status' => $isDone ? 'done' : 'fetching',
            'message' => $stateData['message'],
            'created' => $created,
            'skipped' => $skipped,
            'existing' => $existing,
            'max' => $max,
            'page' => $currentPage,
        ]);
    }

    /**
     * Retorna o progresso atual do seed TMDB.
     */
    public function seedTmdbProgress(): JsonResponse
    {
        $progressFile = storage_path('app/tmdb_seed_progress.json');
        if (!file_exists($progressFile)) {
            return response()->json(['status' => 'idle', 'message' => 'Nenhum seed em execução.']);
        }

        $content = file_get_contents($progressFile);
        if (!$content) {
            return response()->json(['status' => 'idle', 'message' => 'Nenhum seed em execução.']);
        }

        $data = json_decode($content, true);
        if (!$data) {
            return response()->json(['status' => 'error', 'message' => 'Erro ao ler progresso.']);
        }

        // Se terminou, limpa o arquivo
        if (($data['status'] ?? '') === 'done') {
            @unlink($progressFile);
        }

        return response()->json($data);
    }

    /**
     * Cria um filme no banco a partir dos dados da TMDB.
     */
    protected function createMovieFromTmdb(
        array $movie,
        array $categoriaMap,
        int $outrosCategoryId,
        $users,
        TmdbService $tmdb
    ): void {
        $releaseDate = $movie['release_date'] ?? null;
        $ano = $releaseDate ? date('Y', strtotime($releaseDate)) : null;
        $sinopse = $movie['overview'] ?? null;
        $duracao = $movie['runtime'] ?? null;
        $pais = $this->getCountryName($movie['production_countries'] ?? []);
        $voteAverage = $movie['vote_average'] ?? null;
        $voteCount = $movie['vote_count'] ?? null;
        $originalLanguage = $movie['original_language'] ?? null;
        $posterPath = $movie['poster_path'] ?? null;
        $posterUrl = $posterPath ? 'https://image.tmdb.org/t/p/w500'.$posterPath : null;

        $post = Post::create([
            'user_id' => $users->random()->id,
            'name' => $movie['title'] ?? 'Sem título',
            'realname' => $movie['original_title'] ?? null,
            'sinopse' => $sinopse,
            'diretor' => null,
            'duracao' => $duracao,
            'ano' => $ano,
            'pais' => $pais,
            'status1' => 'ativado',
            'status2' => 'aprovado',
            'poster_url' => $posterUrl,
            'vote_average' => $voteAverage,
            'vote_count' => $voteCount,
            'original_language' => $originalLanguage,
            'tmdb_id' => $movie['id'] ?? null,
        ]);

        $genreIds = $movie['genre_ids'] ?? [];
        if (!empty($genreIds)) {
            foreach ($genreIds as $genreId) {
                $categoriaId = $categoriaMap[$genreId] ?? $outrosCategoryId;
                DB::table('categorias_posts')->insert([
                    'post_id' => $post->id,
                    'categoria_id' => $categoriaId,
                ]);
            }
        } else {
            DB::table('categorias_posts')->insert([
                'post_id' => $post->id,
                'categoria_id' => $outrosCategoryId,
            ]);
        }

        $this->fetchAndSaveCast($post, $tmdb);
    }

    protected function fetchAndSaveCast(Post $post, TmdbService $tmdb): void
    {
        try {
            $details = $tmdb->getMovieDetails($post->id);
            $cast = $details['credits']['cast'] ?? [];
            $topCast = array_slice($cast, 0, 10);

            foreach ($topCast as $actor) {
                MovieCast::create([
                    'post_id' => $post->id,
                    'name' => $actor['name'] ?? 'Desconhecido',
                    'character' => $actor['character'] ?? null,
                    'profile_path' => $actor['profile_path'] ?? null,
                    'order' => $actor['order'] ?? 0,
                ]);
            }
        } catch (\Exception $e) {
            report($e);
        }
    }

    protected function getCountryName(array $countries): string
    {
        if (empty($countries)) {
            return 'Estrangeiro';
        }
        $isoCodes = ['US' => 'EUA', 'GB' => 'Reino Unido', 'FR' => 'França', 'DE' => 'Alemanha',
                     'JP' => 'Japão', 'KR' => 'Coreia do Sul', 'CN' => 'China', 'IN' => 'Índia',
                     'BR' => 'Brasil', 'MX' => 'México', 'AR' => 'Argentina', 'IT' => 'Itália',
                     'ES' => 'Espanha', 'AU' => 'Austrália', 'CA' => 'Canadá'];
        $iso = $countries[0]['iso_3166_1'] ?? null;
        return $isoCodes[$iso] ?? ($iso ? $iso : 'Estrangeiro');
    }

    /**
     * Inicia o populador de torrents por lotes via AJAX.
     *
     * Aceita um campo `customUrl` opcional para adicionar um site de torrents customizado.
     */
    public function populateTorrents(Request $request): JsonResponse
    {
        $totalPosts = Post::where('status1', 'ativado')->count();
        if ($totalPosts === 0) {
            return response()->json(['status' => 'error', 'message' => 'Nenhum filme encontrado.'], 400);
        }

        // Validar URL customizada, se fornecida
        $customUrl = trim($request->input('customUrl', ''));
        if ($customUrl !== '' && !filter_var($customUrl, FILTER_VALIDATE_URL)) {
            return response()->json(['status' => 'error', 'message' => 'URL inválida.'], 400);
        }

        $progressFile = storage_path('app/torrent_populate_progress.json');
        file_put_contents($progressFile, json_encode([
            'status' => 'fetching',
            'message' => "Buscando torrents para {$totalPosts} filmes...",
            'processed' => 0,
            'total' => $totalPosts,
            'torrents_saved' => 0,
            'current_page' => 1,
            'custom_url' => $customUrl,
        ], JSON_UNESCAPED_UNICODE));

        return response()->json([
            'status' => 'initialized',
            'message' => "Populador inicializado. {$totalPosts} filmes para processar.",
            'total' => $totalPosts,
        ]);
    }

    /**
     * Processa um lote de filmes buscando torrents.
     */
    public function populateTorrentsBatch(): JsonResponse
    {
        $progressFile = storage_path('app/torrent_populate_progress.json');
        if (!file_exists($progressFile)) {
            return response()->json(['status' => 'error', 'message' => 'Populador não inicializado.']);
        }

        $state = json_decode(file_get_contents($progressFile), true);
        if (!$state) {
            return response()->json(['status' => 'error', 'message' => 'Estado inválido.']);
        }

        $perPage = 10;
        $currentPage = $state['current_page'] ?? 1;
        $processed = $state['processed'] ?? 0;
        $torrentsSaved = $state['torrents_saved'] ?? 0;
        $total = $state['total'] ?? 0;
        $customUrl = $state['custom_url'] ?? '';

        $posts = Post::where('status1', 'ativado')
            ->orderBy('id')
            ->forPage($currentPage, $perPage)
            ->get(['id', 'name', 'realname', 'ano']);

        if ($posts->isEmpty()) {
            // Finalizado
            file_put_contents($progressFile, json_encode([
                'status' => 'done',
                'message' => "Populador concluído! {$torrentsSaved} torrents salvos para {$processed} filmes.",
                'processed' => $processed,
                'total' => $total,
                'torrents_saved' => $torrentsSaved,
            ], JSON_UNESCAPED_UNICODE));
            @unlink($progressFile);
            return response()->json([
                'status' => 'done',
                'message' => "Populador concluído! {$torrentsSaved} torrents salvos.",
                'processed' => $processed,
                'total' => $total,
                'torrents_saved' => $torrentsSaved,
            ]);
        }

        $service = TorrentService::createFromConfig();

        // Adicionar provider customizado se URL fornecida
        if ($customUrl !== '') {
            $customProvider = new GenericWordPressProvider($customUrl);
            $service->addProvider($customProvider);
        }

        foreach ($posts as $post) {
            $searchTerm = $post->realname ?? $post->name;
            if ($post->ano) {
                $searchTerm .= ' ' . $post->ano;
            }

            $results = $service->search($searchTerm);
            $saved = 0;

            foreach ($results as $result) {
                // Evitar duplicatas por magnet
                if (MovieTorrent::where('post_id', $post->id)->where('magnet', $result->magnet)->exists()) {
                    continue;
                }

                $quality = $this->detectQuality($result->title);

                MovieTorrent::create([
                    'post_id' => $post->id,
                    'title' => $result->title,
                    'magnet' => $result->magnet,
                    'size' => $result->size,
                    'quality' => $quality,
                    'seeders' => $result->seeders,
                    'leechers' => $result->leechers,
                    'source' => $result->source,
                ]);
                $saved++;
            }

            $torrentsSaved += $saved;
            $processed++;
        }

        $nextPage = $currentPage + 1;
        $isDone = ($processed >= $total);

        $stateData = [
            'status' => $isDone ? 'done' : 'fetching',
            'message' => $isDone
                ? "Populador concluído! {$torrentsSaved} torrents salvos."
                : "Processando... {$processed}/{$total} filmes, {$torrentsSaved} torrents.",
            'processed' => $processed,
            'total' => $total,
            'torrents_saved' => $torrentsSaved,
            'current_page' => $nextPage,
        ];
        file_put_contents($progressFile, json_encode($stateData, JSON_UNESCAPED_UNICODE));

        if ($isDone) {
            @unlink($progressFile);
        }

        return response()->json([
            'status' => $isDone ? 'done' : 'fetching',
            'message' => $stateData['message'],
            'processed' => $processed,
            'total' => $total,
            'torrents_saved' => $torrentsSaved,
        ]);
    }

    /**
     * Detecta a qualidade do torrent a partir do título.
     */
    protected function detectQuality(string $title): ?string
    {
        $upper = strtoupper($title);

        if (preg_match('/\b(4K|2160P|UHD)\b/', $upper)) {
            return '4K';
        }
        if (preg_match('/\b(1080P|FULL\s*HD|FHD)\b/', $upper)) {
            return '1080p';
        }
        if (preg_match('/\b(720P|HD)\b/', $upper)) {
            return '720p';
        }
        if (preg_match('/\b(480P|SD)\b/', $upper)) {
            return '480p';
        }
        if (preg_match('/\b(BLU\s*RAY|BLURAY|BR)\b/', $upper)) {
            return 'BluRay';
        }
        if (preg_match('/\b(WEB\s*DL|WEBDL|WEBRIP)\b/', $upper)) {
            return 'WEB-DL';
        }

        return null;
    }

    /**
     * Retorna o progresso atual do populador de torrents.
     */
    public function populateTorrentsProgress(): JsonResponse
    {
        $progressFile = storage_path('app/torrent_populate_progress.json');
        if (!file_exists($progressFile)) {
            return response()->json(['status' => 'idle', 'message' => 'Nenhum populador em execução.']);
        }

        $content = file_get_contents($progressFile);
        if (!$content) {
            return response()->json(['status' => 'idle', 'message' => 'Nenhum populador em execução.']);
        }

        $data = json_decode($content, true);
        if (!$data) {
            return response()->json(['status' => 'error', 'message' => 'Erro ao ler progresso.']);
        }

        if (($data['status'] ?? '') === 'done') {
            @unlink($progressFile);
        }

        return response()->json($data);
    }

    /**
     * Faz o download do dump SQL gerado pelo comando db:dump.
     */
    public function downloadDump(Request $request): Response|RedirectResponse
    {
        $filepath = session('db_dump_file');
        $filename = session('db_dump_filename');

        if (!$filepath || !file_exists($filepath)) {
            return redirect()->route('adm.console')
                ->with('console_output', 'Nenhum dump SQL encontrado. Execute primeiro o comando "Exportar banco SQL".')
                ->with('console_exit_code', 1)
                ->with('console_command_name', 'Download dump');
        }

        return response()->download($filepath, $filename ?? 'filmes100ads.sql');
    }
}
