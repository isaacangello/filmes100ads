<?php

namespace App\Livewire;

use App\Models\Post;
use App\Services\EarningsService;
use App\Services\Torrent\TorrentService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Página de detalhe de um filme (disponível na rota /?filmeid=ID).
 */
class MovieDetail extends Component
{
    /** Id do filme exibido. */
    public $filmeid;

    /** Resultados da busca de torrents (arrays serializáveis). */
    public array $torrentResults = [];

    /** Magnet link salvo ou encontrado. */
    public ?string $magnetLink = null;

    public function mount($filmeid)
    {
        $this->filmeid = (int) $filmeid;

        // Registrar visualização do filme
        $this->trackView();

        // Buscar torrents se não houver magnet salvo
        $this->fetchTorrents();
    }

    /**
     * Registra a visualização do filme para cálculo de rendimentos.
     */
    protected function trackView(): void
    {
        try {
            $service = app(EarningsService::class);
            $userId = auth()->id();
            $ip = request()->ip();

            $service->trackView($this->filmeid, $userId, $ip);
        } catch (\Exception $e) {
            // Erro no tracking não deve quebrar a página
        }
    }

    /**
     * Busca torrents para o filme e salva o melhor magnet link.
     */
    protected function fetchTorrents(): void
    {
        $filme = Post::find($this->filmeid);

        if (! $filme) {
            return;
        }

        // Se já tem magnet salvo, usa diretamente
        if ($filme->magnet_link) {
            $this->magnetLink = $filme->magnet_link;
            return;
        }

        // Construir o termo de busca
        $termo = trim(($filme->realname ?: $filme->name).' '.($filme->ano ?? ''));

        if ($termo === '') {
            return;
        }

        try {
            $service = TorrentService::createFromConfig();
            $results = $service->search($termo);

            if (empty($results)) {
                return;
            }

            // Ordenar por seeders (decrescente)
            usort($results, fn ($a, $b) => $b->seeders <=> $a->seeders);

            // Salvar o melhor magnet link
            $best = $results[0];
            $filme->magnet_link = $best->magnet;
            $filme->save();

            $this->magnetLink = $best->magnet;
            // Converter para arrays para serialização Livewire
            $this->torrentResults = array_map(fn ($r) => [
                'title' => $r->title,
                'magnet' => $r->magnet,
                'size' => $r->size,
                'seeders' => $r->seeders,
                'leechers' => $r->leechers,
                'source' => $r->source,
                'url' => $r->url,
                'category' => $r->category,
            ], $results);
        } catch (\Exception $e) {
            // Erro na busca não deve quebrar a página
        }
    }

    public function render()
    {
        $filme = Post::with(['cast', 'torrents'])->find($this->filmeid);

        abort_if(! $filme, 404, 'Filme não encontrado.');

        /* Categoria do filme. */
        $categoria = DB::table('categorias_posts')
            ->join('categorias', 'categorias_posts.categoria_id', '=', 'categorias.id')
            ->where('categorias_posts.post_id', $this->filmeid)
            ->value('categorias.name');

        // Agrupar torrents por qualidade
        $torrentsByQuality = $filme->torrents->groupBy('quality')->map(function ($group) {
            return $group->sortByDesc('seeders')->values();
        });

        // Últimos 10 filmes adicionados do ano corrente
        $anoCorrente = (int) date('Y');
        $ultimosAdicionados = Post::where('ano', $anoCorrente)
            ->where('status1', 'ativado')
            ->where('id', '!=', $this->filmeid)
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'name', 'poster_url', 'ano']);

        return view('livewire.movie-detail', [
            'filme' => $filme,
            'categoria' => $categoria,
            'torrentsByQuality' => $torrentsByQuality,
            'ultimosAdicionados' => $ultimosAdicionados,
        ]);
    }
}
