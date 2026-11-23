<?php

namespace App\Console\Commands;

use App\Models\Categoria;
use App\Models\MovieCast;
use App\Models\Post;
use App\Models\User;
use App\Services\TmdbService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Comando para popular o banco com filmes da TMDB API.
 *
 * Envia progresso via arquivo temporário para que a interface web
 * possa exibir uma barra de progresso em tempo real.
 */
class SeedTmdb extends Command
{
    protected $signature = 'db:seed-tmdb {--max=10000 : Número máximo de filmes}';

    protected $description = 'Busca filmes da TMDB API e salva no banco com progresso em tempo real';

    /**
     * Caminho do arquivo de progresso.
     */
    protected string $progressFile;

    public function handle(): int
    {
        $tmdb = new TmdbService();

        if (! $tmdb->isConfigured()) {
            $this->error('TMDB_API_KEY não configurado no .env.');
            return self::FAILURE;
        }

        $maxMovies = (int) $this->option('max');
        $startYear = 2006;
        $endYear = 2026;

        // Arquivo de progresso para comunicação com a view web
        $this->progressFile = storage_path('app/tmdb_seed_progress.json');

        $genreMapping = [
            28 => 'Ação',
            16 => 'Animação',
            35 => 'Comédia',
            10749 => 'Comédia romântica',
            18 => 'Drama',
            10752 => 'Documentário',
            53 => 'Suspense',
            80 => 'Filme policial',
            9648 => 'Suspense',
            12 => 'Ficção científica',
            14 => 'Ficção científica',
            36 => 'Filmes de guerra',
            10402 => 'Musical',
            10751 => 'Comédia romântica',
            878 => 'Ficção científica',
            99 => 'Documentário',
            10770 => 'Terror',
            27 => 'Terror',
            37 => 'Faroeste (ou western)',
        ];

        $this->writeProgress(['status' => 'starting', 'message' => 'Iniciando seed de filmes da TMDB API...']);

        // Limpa dados existentes
        $this->writeProgress(['status' => 'cleaning', 'message' => 'Limpando dados existentes...']);
        DB::table('movie_views')->delete();
        DB::table('movie_cast')->delete();
        DB::table('urls')->delete();
        DB::table('categorias_posts')->delete();
        DB::table('posts')->delete();
        DB::table('categorias')->delete();

        // Cria categorias
        $this->writeProgress(['status' => 'categories', 'message' => 'Criando categorias...']);
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

        // Busca usuários
        $users = User::all();
        if ($users->isEmpty()) {
            $this->writeProgress(['status' => 'error', 'message' => 'Nenhum usuário encontrado. Crie um usuário admin primeiro.']);
            $this->error('Nenhum usuário encontrado.');
            return self::FAILURE;
        }

        // Busca filmes
        $startDate = "{$startYear}-01-01";
        $endDate = "{$endYear}-12-31";

        $this->writeProgress(['status' => 'searching', 'message' => "Buscando filmes lançados entre {$startDate} e {$endDate}..."]);

        $page = 1;
        $moviesCreated = 0;
        $moviesSkipped = 0;

        // Primeira página para obter total
        $firstPage = $tmdb->getMoviesByReleaseDate($startDate, $endDate, 1);
        $totalResults = $firstPage['total_results'] ?? 0;

        $this->writeProgress([
            'status' => 'fetching',
            'message' => "Encontrados {$totalResults} filmes na TMDB. Buscando até {$maxMovies}...",
            'created' => 0,
            'skipped' => 0,
            'max' => $maxMovies,
            'total_found' => $totalResults
        ]);

        // Processa a primeira página
        $moviesList = $firstPage['results'] ?? [];
        if (!empty($moviesList)) {
            foreach ($moviesList as $movie) {
                if ($moviesCreated >= $maxMovies) break;
                $releaseDate = $movie['release_date'] ?? null;
                if (empty($releaseDate)) {
                    $moviesSkipped++;
                    continue;
                }
                try {
                    $this->createMovieFromTmdb($movie, $categoriaMap, $outrosCategoria->id, $users, $tmdb);
                    $moviesCreated++;
                    $this->writeProgress([
                        'status' => 'fetching',
                        'message' => "Processando filmes... {$moviesCreated}/{$maxMovies}",
                        'created' => $moviesCreated,
                        'skipped' => $moviesSkipped,
                        'max' => $maxMovies,
                        'total_found' => $totalResults
                    ]);
                } catch (\Exception $e) {
                    $moviesSkipped++;
                    report($e);
                }
            }
        }

        $page++;
        $totalPages = $firstPage['total_pages'] ?? 1;

        // Páginas restantes
        while ($moviesCreated < $maxMovies && $page <= $totalPages) {
            $pageResponse = $tmdb->getMoviesByReleaseDate($startDate, $endDate, $page);
            $moviesList = $pageResponse['results'] ?? [];

            if (empty($moviesList)) break;

            foreach ($moviesList as $movie) {
                if ($moviesCreated >= $maxMovies) break;
                $releaseDate = $movie['release_date'] ?? null;
                if (empty($releaseDate)) {
                    $moviesSkipped++;
                    continue;
                }
                try {
                    $this->createMovieFromTmdb($movie, $categoriaMap, $outrosCategoria->id, $users, $tmdb);
                    $moviesCreated++;
                    // Atualiza progresso a cada 50 filmes para não sobrecarregar
                    if ($moviesCreated % 50 === 0) {
                        $this->writeProgress([
                            'status' => 'fetching',
                            'message' => "Processando filmes... {$moviesCreated}/{$maxMovies}",
                            'created' => $moviesCreated,
                            'skipped' => $moviesSkipped,
                            'max' => $maxMovies,
                            'total_found' => $totalResults
                        ]);
                    }
                } catch (\Exception $e) {
                    $moviesSkipped++;
                    report($e);
                }
            }

            $page++;
            usleep(50000); // 50ms delay para rate limit
        }

        // Finaliza
        $this->writeProgress([
            'status' => 'done',
            'message' => "Seed concluído! {$moviesCreated} filmes criados, {$moviesSkipped} pulados.",
            'created' => $moviesCreated,
            'skipped' => $moviesSkipped,
            'max' => $maxMovies,
            'total_found' => $totalResults
        ]);

        // Remove arquivo de progresso após 5 segundos (para a view ler)
        // A view vai limpar o arquivo após ler o status 'done'

        $this->info("Seed concluído!");
        $this->info("Filmes criados: {$moviesCreated}");
        $this->info("Filmes pulados: {$moviesSkipped}");

        return self::SUCCESS;
    }

    /**
     * Escreve o progresso no arquivo temporário.
     */
    protected function writeProgress(array $data): void
    {
        file_put_contents($this->progressFile, json_encode($data, JSON_UNESCAPED_UNICODE));
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
}
