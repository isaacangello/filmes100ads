<?php

namespace App\Services\Torrent;

use App\Services\Torrent\Providers\ArchiveOrgProvider;
use App\Services\Torrent\Providers\BaixetorrentProvider;
use App\Services\Torrent\Providers\BitSearchProvider;
use App\Services\Torrent\Providers\NyaaProvider;
use App\Services\Torrent\Providers\SolidTorrentsProvider;
use Illuminate\Support\Facades\Cache;

/**
 * Service que orquestra a busca de torrents entre múltiplos providers.
 *
 * Cada provider é consultado em sequência, com timeout e cache dos resultados.
 */
class TorrentService
{
    /** @var TorrentProviderInterface[] */
    protected array $providers = [];

    /** Tempo em segundos para cachear os resultados. */
    protected int $cacheTtl;

    public function __construct(array $providers = [], int $cacheTtl = 300)
    {
        $this->providers = $providers;
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * Cria uma instância do service usando a configuração do arquivo `config/torrents.php`.
     */
    public static function createFromConfig(): self
    {
        $config = config('torrents.providers', []);
        $providers = [];

        foreach ($config as $name => $providerConfig) {
            if (! ($providerConfig['enabled'] ?? true)) {
                continue;
            }

            $provider = match ($name) {
                'bitsearch' => new BitSearchProvider($providerConfig),
                'solidtorrents' => new SolidTorrentsProvider($providerConfig),
                'nyaa' => new NyaaProvider($providerConfig),
                'archiveorg' => new ArchiveOrgProvider($providerConfig),
                'baixetorrent' => new BaixetorrentProvider($providerConfig),
                default => null,
            };

            if ($provider !== null) {
                $providers[] = $provider;
            }
        }

        return new self($providers, (int) ($config['_cache_ttl'] ?? 300));
    }

    /**
     * Busca torrents pelo termo informado, agregando resultados de todos os providers.
     *
     * @return TorrentResult[]
     */
    public function search(string $query, ?string $sourceFilter = null): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        // Cache por termo de busca para evitar chamadas repetidas
        $cacheKey = 'torrent_search:'.$query.':'.$sourceFilter;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($query, $sourceFilter) {
            $allResults = [];

            foreach ($this->providers as $provider) {
                // Se houver filtro por fonte, pular providers não selecionados
                if ($sourceFilter !== null && $provider->getName() !== $sourceFilter) {
                    continue;
                }

                $results = $provider->search($query);
                $allResults = array_merge($allResults, $results);
            }

            return $allResults;
        });
    }

    /**
     * Adiciona um provider dinamicamente.
     * Útil para providers customizados criados pelo usuário (ex: URL digitada no console ADM).
     */
    public function addProvider(TorrentProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * Retorna a lista de nomes dos providers disponíveis.
     *
     * @return string[]
     */
    public function getAvailableSources(): array
    {
        return array_map(
            fn (TorrentProviderInterface $p) => $p->getName(),
            $this->providers
        );
    }

    /**
     * Limpa o cache de busca.
     */
    public function clearCache(): void
    {
        Cache::forget('torrent_search:*');
    }
}
