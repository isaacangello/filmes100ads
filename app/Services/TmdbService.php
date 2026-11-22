<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Serviço para buscar dados de filmes na TMDB API.
 *
 * Documentação: https://developer.themoviedb.org/reference
 */
class TmdbService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.tmdb.base_url', 'https://api.themoviedb.org/3');
        $this->apiKey = config('services.tmdb.api_key', '');
    }

    /**
     * Verifica se a API key está configurada.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Busca filmes populares com paginação.
     *
     * @param int $page Página para buscar
     * @param string $region Região para filtrar (ex: 'BR')
     * @return array Lista de filmes
     */
    public function getPopularMovies(int $page = 1, string $region = 'BR'): array
    {
        $response = $this->request('movie/popular', [
            'page' => $page,
            'region' => $region,
            'language' => 'pt-BR',
        ]);

        return $response['results'] ?? [];
    }

    /**
     * Busca filmes por data de lançamento.
     * Retorna a resposta completa incluindo total_results, total_pages e results.
     *
     * @param string $primaryReleaseDateGte Data mínima (YYYY-MM-DD)
     * @param string $primaryReleaseDateLte Data máxima (YYYY-MM-DD)
     * @param int $page Página para buscar
     * @return array Resposta completa com 'results', 'total_results', 'total_pages'
     */
    public function getMoviesByReleaseDate(
        string $primaryReleaseDateGte,
        string $primaryReleaseDateLte,
        int $page = 1
    ): array {
        return $this->request('discover/movie', [
            'page' => $page,
            'primary_release_date.gte' => $primaryReleaseDateGte,
            'primary_release_date.lte' => $primaryReleaseDateLte,
            'language' => 'pt-BR',
            'sort_by' => 'popularity.desc',
        ]);
    }

    /**
     * Busca detalhes completos de um filme.
     *
     * @param int $movieId ID do filme na TMDB
     * @return array Detalhes do filme
     */
    public function getMovieDetails(int $movieId): array
    {
        return $this->request("movie/{$movieId}", [
            'language' => 'pt-BR',
            'append_to_response' => 'videos,credits',
        ]);
    }

    /**
     * Busca trailers de um filme.
     *
     * @param int $movieId ID do filme na TMDB
     * @return array Lista de trailers
     */
    public function getMovieTrailers(int $movieId): array
    {
        $response = $this->request("movie/{$movieId}/videos", [
            'language' => 'pt-BR',
        ]);

        $videos = $response['results'] ?? [];

        // Filtra apenas trailers em YouTube
        return array_values(array_filter($videos, function ($video) {
            return $video['site'] === 'YouTube'
                && in_array(strtoupper($video['type']), ['TRAILER', 'TEASER', 'FEATURETRAILER']);
        }));
    }

    /**
     * Busca gêneros disponíveis.
     *
     * @return array Lista de gêneros
     */
    public function getGenres(): array
    {
        $response = $this->request('genre/movie/list', [
            'language' => 'pt-BR',
        ]);

        return $response['genres'] ?? [];
    }

    /**
     * Faz uma requisição à API TMDB.
     *
     * @param string $endpoint Endpoint da API
     * @param array $params Parâmetros adicionais
     * @return array Resposta decodificada
     */
    protected function request(string $endpoint, array $params = []): array
    {
        if (empty($this->apiKey)) {
            return [];
        }

        $params['api_key'] = $this->apiKey;

        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/{$endpoint}", $params);

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Exception $e) {
            report($e);
            return [];
        }
    }
}
