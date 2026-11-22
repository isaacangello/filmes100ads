<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\TmdbService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Comando para popular o campo poster_url dos filmes existentes.
 *
 * Busca cada filme na TMDB pelo título e ano, e salva a URL da capa.
 */
class PopulatePosters extends Command
{
    /**
     * O nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'db:populate-posters {--limit=10000 : Número máximo de filmes para processar}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Popular o campo poster_url dos filmes existentes usando a TMDB API';

    /**
     * Executa o comando.
     */
    public function handle(TmdbService $tmdb): int
    {
        // Verifica se a API key está configurada
        if (! $tmdb->isConfigured()) {
            $this->error('TMDB_API_KEY não está configurado no .env.');
            return Command::FAILURE;
        }

        $limit = (int) $this->option('limit');
        $posts = Post::whereNull('poster_url')->limit($limit)->get();

        if ($posts->isEmpty()) {
            $this->info('Não há filmes sem poster_url.');
            return Command::SUCCESS;
        }

        $this->info("Encontrados {$posts->count()} filmes sem capa.");

        $bar = $this->output->createProgressBar($posts->count());
        $updated = 0;
        $skipped = 0;

        foreach ($posts as $post) {
            // Constrói o termo de busca: realname ou name + ano
            $title = $post->realname ?? $post->name;
            $year = $post->ano ?? '';
            $query = $title;
            if ($year) {
                $query .= " ({$year})";
            }

            try {
                // Busca o filme na TMDB usando o título original
                $baseUrl = config('services.tmdb.base_url', 'https://api.themoviedb.org/3');
                $apiKey = config('services.tmdb.api_key', '');

                // Tenta primeiro com o realname (título original)
                $searchTitle = $post->realname ?? $post->name;
                $response = Http::timeout(10)->get("{$baseUrl}/search/movie", [
                    'api_key' => $apiKey,
                    'query' => $searchTitle,
                    'language' => 'en-US',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $results = $data['results'] ?? [];

                    // Se tem ano, tenta encontrar um resultado que combine
                    if (! empty($results) && $post->ano) {
                        $matched = null;
                        foreach ($results as $movie) {
                            $releaseYear = $movie['release_date'] ? date('Y', strtotime($movie['release_date'])) : null;
                            if ($releaseYear == $post->ano) {
                                $matched = $movie;
                                break;
                            }
                        }
                        $movie = $matched ?? $results[0];
                    } elseif (! empty($results)) {
                        $movie = $results[0];
                    } else {
                        $movie = null;
                    }

                    if ($movie !== null && isset($movie['poster_path']) && $movie['poster_path']) {
                        $posterUrl = 'https://image.tmdb.org/t/p/w500'.$movie['poster_path'];
                        $post->update(['poster_url' => $posterUrl]);
                        $updated++;
                    } else {
                        $skipped++;
                    }
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $skipped++;
                report($e);
            }

            $bar->advance();

            // Delay para respeitar rate limit da TMDB (40 req/seg)
            usleep(30000); // 30ms
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Concluído!");
        $this->info("Capas atualizadas: {$updated}");
        $this->info("Pulados (sem capa ou erro): {$skipped}");

        return Command::SUCCESS;
    }
}
