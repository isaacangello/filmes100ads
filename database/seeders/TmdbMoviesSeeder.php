<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\MovieCast;
use App\Models\Post;
use App\Models\User;
use App\Services\TmdbService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder que busca filmes reais da TMDB API.
 *
 * Requer TMDB_API_KEY configurado no .env.
 * Obter em: https://www.themoviedb.org/settings/api
 */
class TmdbMoviesSeeder extends Seeder
{
    /**
     * Quantidade máxima de filmes para buscar.
     */
    protected int $maxMovies = 20000;

    /**
     * Ano inicial para filtrar (20 anos atrás de 2026).
     */
    protected int $startYear = 2006;

    /**
     * Ano final para filtrar.
     */
    protected int $endYear = 2026;

    /**
     * Mapeamento de gêneros TMDB para categorias do projeto.
     */
    protected array $genreMapping = [
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
        53 => 'Suspense',
        27 => 'Terror',
        37 => 'Faroeste (ou western)',
        10752 => 'Documentário',
        35 => 'Comédia',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tmdb = new TmdbService();

        if (! $tmdb->isConfigured()) {
            $this->command->error('TMDB_API_KEY não configurado no .env.');
            $this->command->info('Obtenha uma chave gratuita em: https://www.themoviedb.org/settings/api');
            return;
        }

        $this->command->info('Iniciando seed de filmes da TMDB API...');
        $this->command->info("Período: {$this->startYear} a {$this->endYear}");
        $this->command->info("Máximo de filmes: {$this->maxMovies}");

        // Limpa dados existentes (usa DB::table para evitar foreign key constraints)
        $this->command->info('Limpando dados existentes...');
        DB::table('movie_views')->delete();
        DB::table('movie_cast')->delete();
        DB::table('urls')->delete();
        DB::table('categorias_posts')->delete();
        DB::table('posts')->delete();
        DB::table('categorias')->delete();

        // Cria categorias baseadas nos gêneros TMDB
        $this->command->info('Buscando gêneros da TMDB...');
        $genres = $tmdb->getGenres();
        $categoriaMap = [];

        foreach ($this->genreMapping as $tmdbGenreId => $categoriaName) {
            $categoria = Categoria::firstOrCreate(
                ['name' => $categoriaName],
                ['detalhes' => "Categoria importada da TMDB API.", 'status1' => 'ativo']
            );
            $categoriaMap[$tmdbGenreId] = $categoria->id;
        }

        // Adiciona categoria padrão
        $outrosCategoria = Categoria::firstOrCreate(
            ['name' => 'Outros'],
            ['detalhes' => 'Categoria Padrão, que vão os filmes sem categoria', 'status1' => 'ativo']
        );

        // Busca usuários para distribuir os filmes
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->error('Nenhum usuário encontrado. Execute o UsersTableSeeder primeiro.');
            return;
        }

        // Busca filmes por data de lançamento
        $startDate = "{$this->startYear}-01-01";
        $endDate = "{$this->endYear}-12-31";

        $this->command->info("Buscando filmes lançados entre {$startDate} e {$endDate}...");

        $page = 1;
        $moviesCreated = 0;
        $moviesSkipped = 0;

        // Barra de progresso
        $bar = $this->command->getOutput()->createProgressBar($this->maxMovies);
        $bar->start();

        // Busca total_pages na primeira requisição
        $firstPage = $tmdb->getMoviesByReleaseDate($startDate, $endDate, 1);
        $totalPages = $firstPage['total_pages'] ?? 1;
        $totalResults = $firstPage['total_results'] ?? 0;

        $this->command->info("Total de filmes encontrados na TMDB: {$totalResults}");
        $this->command->info("Páginas totais: {$totalPages}");

        // Processa a primeira página
        $moviesList = $firstPage['results'] ?? [];
        if (!empty($moviesList)) {
            foreach ($moviesList as $movie) {
                if ($moviesCreated >= $this->maxMovies) {
                    break;
                }
                // discover/movie retorna 'release_date', não 'primary_release_date'
                $releaseDate = $movie['release_date'] ?? null;
                if (empty($releaseDate)) {
                    $moviesSkipped++;
                    continue;
                }
                try {
                    $this->createMovieFromTmdb($movie, $categoriaMap, $outrosCategoria->id, $users, $tmdb);
                    $moviesCreated++;
                    $bar->advance();
                } catch (\Exception $e) {
                    $moviesSkipped++;
                    report($e);
                }
            }
        }

        // Incrementa página após processar a primeira
        $page++;

        // Busca as páginas restantes
        while ($moviesCreated < $this->maxMovies && $page <= $totalPages) {
            $pageResponse = $tmdb->getMoviesByReleaseDate($startDate, $endDate, $page);
            $moviesList = $pageResponse['results'] ?? [];

            if (empty($moviesList)) {
                break;
            }

            foreach ($moviesList as $movie) {
                if ($moviesCreated >= $this->maxMovies) {
                    break;
                }
                $releaseDate = $movie['release_date'] ?? null;
                if (empty($releaseDate)) {
                    $moviesSkipped++;
                    continue;
                }
                try {
                    $this->createMovieFromTmdb($movie, $categoriaMap, $outrosCategoria->id, $users, $tmdb);
                    $moviesCreated++;
                    $bar->advance();
                } catch (\Exception $e) {
                    $moviesSkipped++;
                    report($e);
                }
            }

            $page++;

            // Pequeno delay para respeitar rate limit (40 req/seg)
            usleep(50000); // 50ms
        }

        $bar->finish();
        $this->command->newLine(2);

        $this->command->info("Seed concluído!");
        $this->command->info("Filmes criados: {$moviesCreated}");
        $this->command->info("Filmes pulados: {$moviesSkipped}");
    }

    /**
     * Cria um filme no banco a partir dos dados da TMDB.
     *
     * @param array $movie Dados do filme da TMDB
     * @param array $categoriaMap Mapeamento de gêneros TMDB para categorias
     * @param int $outrosCategoryId ID da categoria "Outros"
     * @param \Illuminate\Support\Collection $users Coleção de usuários
     * @param TmdbService $tmdb Serviço TMDB
     * @return void
     */
    protected function createMovieFromTmdb(
        array $movie,
        array $categoriaMap,
        int $outrosCategoryId,
        $users,
        TmdbService $tmdb
    ): void {
        // Extrai dados do filme (discover/movie usa 'release_date')
        $releaseDate = $movie['release_date'] ?? null;
        $ano = $releaseDate ? date('Y', strtotime($releaseDate)) : null;

        // Usa os dados da lista (já incluem título, original_title, overview, runtime)
        $sinopse = $movie['overview'] ?? null;
        $duracao = $movie['runtime'] ?? null;
        $pais = $this->getCountryName($movie['production_countries'] ?? []);

        // Dados de avaliação e idioma
        $voteAverage = $movie['vote_average'] ?? null;
        $voteCount = $movie['vote_count'] ?? null;
        $originalLanguage = $movie['original_language'] ?? null;

        // Constrói a URL da capa da TMDB (poster_path)
        $posterPath = $movie['poster_path'] ?? null;
        $posterUrl = $posterPath ? 'https://image.tmdb.org/t/p/w500'.$posterPath : null;

        // Cria o post (filme)
        $post = Post::create([
            'user_id' => $users->random()->id,
            'name' => $movie['title'] ?? 'Sem título',
            'realname' => $movie['original_title'] ?? null,
            'sinopse' => $sinopse,
            'diretor' => null, // Não disponível na lista, seria chamada extra
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

        // Associa categorias baseadas nos gêneros TMDB
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

        // Busca elenco do filme (top 10 atores) — chamada extra à API
        $this->fetchAndSaveCast($post, $tmdb);
    }

    /**
     * Busca e salva o elenco principal do filme na TMDB.
     *
     * @param Post $post Filme salvo
     * @param TmdbService $tmdb Serviço TMDB
     * @return void
     */
    protected function fetchAndSaveCast(Post $post, TmdbService $tmdb): void
    {
        try {
            $details = $tmdb->getMovieDetails($post->id);
            $cast = $details['credits']['cast'] ?? [];

            // Pega apenas os top 10 atores para não sobrecarregar o banco
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
            // Erro ao buscar elenco não deve quebrar o seeder
            report($e);
        }
    }

    /**
     * Extrai o nome do país a partir dos dados da TMDB.
     *
     * @param array $countries Lista de países da TMDB
     * @return string Nome do país
     */
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
