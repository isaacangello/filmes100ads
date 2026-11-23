<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Catálogo público de filmes: filtro por categoria, busca por título e paginação.
 */
class MovieCatalog extends Component
{
    use WithPagination;

    /** Filmes exibidos por página. */
    public const PER_PAGE = 30;

    /** Categoria selecionada (?categorias=ID). */
    #[Url(as: 'categorias', except: '')]
    public $categoriaId = '';

    /** Termo de busca por título (?busca=TERMO). */
    #[Url(as: 'busca', except: '')]
    public $busca = '';

    public function paginationView()
    {
        return 'pagination::fda';
    }

    public function updatedCategoriaId()
    {
        $this->resetPage();
    }

    public function updatedBusca()
    {
        $this->resetPage();
    }

    /**
     * Limpa todos os filtros aplicados.
     */
    public function limparFiltros()
    {
        $this->reset('categoriaId', 'busca');
        $this->resetPage();
    }

    public function render()
    {
        $categorias = Categoria::orderBy('name')->get();

        $categoria = $this->categoriaId !== ''
            ? $categorias->firstWhere('id', (int) $this->categoriaId)
            : null;

        $query = Post::query()
            ->with('categorias:id,name')
            ->select('id', 'name', 'sinopse', 'ano', 'duracao', 'poster_url');

        if ($this->categoriaId !== '') {
            $query->whereIn('id', DB::table('categorias_posts')
                ->select('post_id')
                ->where('categoria_id', (int) $this->categoriaId));
        }

        $termo = trim((string) $this->busca);

        if ($termo !== '') {
            // FULLTEXT em MySQL; fallback para LIKE em SQLite (testes)
            if (config('database.default') === 'sqlite') {
                $query->where('name', 'like', '%'.$termo.'%');
            } else {
                $query->whereRaw('MATCH(name, realname) AGAINST (? IN BOOLEAN MODE)', [$termo]);
            }
        }

        $posts = $query->orderBy('id')->paginate(self::PER_PAGE);

        return view('livewire.movie-catalog', [
            'posts' => $posts,
            'categorias' => $categorias,
            'categoria' => $categoria,
            'total' => $posts->total(),
        ]);
    }
}
