<?php

namespace App\Livewire;

use App\Services\Torrent\TorrentResult;
use App\Services\Torrent\TorrentService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Componente Livewire para busca de torrents.
 *
 * Permite buscar por termo, filtrar por fonte e ordenar os resultados.
 */
class TorrentSearch extends Component
{
    use WithPagination;

    /** Filmes exibidos por página. */
    public const PER_PAGE = 15;

    /** Termo de busca (?busca=TERMO). */
    #[Url(as: 'busca', except: '')]
    public $busca = '';

    /** Filtro por fonte específica (?fonte=nome). */
    #[Url(as: 'fonte', except: '')]
    public $fonte = '';

    /** Ordenação dos resultados (?ordem=seeders). */
    #[Url(as: 'ordem', except: 'seeders')]
    public $ordem = 'seeders';

    /** Resultados da busca (arrays serializáveis). */
    public array $resultados = [];

    /** Lista de fontes disponíveis. */
    public array $fontesDisponiveis = [];

    /** Mensagem de erro, se houver. */
    public string $erro = '';

    /** Indica se a busca está em andamento. */
    public bool $buscando = false;

    /**
     * Inicializa o componente com as fontes disponíveis.
     */
    public function mount(?string $termo = null): void
    {
        $service = TorrentService::createFromConfig();
        $this->fontesDisponiveis = $service->getAvailableSources();

        // Termo inicial da página dedicada do filme
        if ($termo !== null && $termo !== '') {
            $this->busca = $termo;
        }

        // Se já há termo de busca, executar a busca imediatamente
        if ($this->busca !== '') {
            $this->executarBusca();
        }
    }

    /**
     * Executa a busca quando o termo é alterado.
     */
    public function updatedBusca(): void
    {
        $this->resetPage();
        $this->executarBusca();
    }

    /**
     * Executa a busca quando a fonte é alterada.
     */
    public function updatedFonte(): void
    {
        $this->resetPage();
        if ($this->busca !== '') {
            $this->executarBusca();
        }
    }

    /**
     * Executa a busca quando a ordenação é alterada.
     */
    public function updatedOrdem(): void
    {
        $this->ordenarResultados();
    }

    /**
     * Executa a busca nos providers configurados.
     */
    public function buscar(): void
    {
        $this->executarBusca();
    }

    /**
     * Limpa a busca e os resultados.
     */
    public function limparBusca(): void
    {
        $this->reset('busca', 'fonte', 'resultados', 'erro');
        $this->resetPage();
    }

    protected function executarBusca(): void
    {
        $termo = trim((string) $this->busca);

        if ($termo === '') {
            $this->resultados = [];
            $this->erro = '';
            return;
        }

        $this->buscando = true;
        $this->erro = '';

        try {
            $service = TorrentService::createFromConfig();
            $fonteFilter = $this->fonte !== '' ? $this->fonte : null;

            $torrentResults = $service->search($termo, $fonteFilter);
            // Converter TorrentResult[] para array[] para serialização Livewire
            $this->resultados = array_map(fn (TorrentResult $r) => [
                'title' => $r->title,
                'magnet' => $r->magnet,
                'size' => $r->size,
                'seeders' => $r->seeders,
                'leechers' => $r->leechers,
                'source' => $r->source,
                'url' => $r->url,
                'category' => $r->category,
            ], $torrentResults);
            $this->ordenarResultados();

            if (empty($this->resultados)) {
                $this->erro = 'Nenhum resultado encontrado para "'.$termo.'".';
            }
        } catch (\Exception $e) {
            $this->erro = 'Erro ao buscar torrents. Tente novamente mais tarde.';
        } finally {
            $this->buscando = false;
        }
    }

    protected function ordenarResultados(): void
    {
        match ($this->ordem) {
            'seeders' => usort($this->resultados, fn ($a, $b) => $b['seeders'] <=> $a['seeders']),
            'size' => usort($this->resultados, fn ($a, $b) => $this->parseSize($b['size']) <=> $this->parseSize($a['size'])),
            'title' => usort($this->resultados, fn ($a, $b) => strcmp($a['title'], $b['title'])),
            default => usort($this->resultados, fn ($a, $b) => $b['seeders'] <=> $a['seeders']),
        };
    }

    /**
     * Converte string de tamanho para bytes para comparação.
     */
    protected function parseSize(string $size): int
    {
        $size = strtolower(trim($size));

        if ($size === '' || $size === '—') {
            return 0;
        }

        $units = [
            'tb' => 1099511627776,
            'gb' => 1073741824,
            'mb' => 1048576,
            'kb' => 1024,
            'b' => 1,
        ];

        foreach ($units as $unit => $multiplier) {
            if (str_contains($size, $unit)) {
                $number = (float) str_replace($unit, '', $size);
                return (int) ($number * $multiplier);
            }
        }

        // Tentar parsear como número puro (bytes)
        return (int) filter_var($size, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Retorna o label amigável para uma fonte.
     */
    public function getFonteLabel(string $fonte): string
    {
        return match ($fonte) {
            'solidtorrents' => 'SolidTorrents',
            'nyaa' => 'Nyaa',
            'archiveorg' => 'Archive.org',
            default => ucfirst($fonte),
        };
    }

    /**
     * Retorna o ícone para uma fonte.
     */
    public function getFonteIcon(string $fonte): string
    {
        return match ($fonte) {
            'solidtorrents' => 'fa-layer-group',
            'nyaa' => 'fa-ghost',
            'archiveorg' => 'fa-box-archive',
            default => 'fa-download',
        };
    }

    public function render()
    {
        return view('livewire.torrent-search');
    }
}
