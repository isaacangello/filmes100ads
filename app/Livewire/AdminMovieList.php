<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Lista dos filmes cadastrados pelo usuário logado (área ADM).
 *
 * Suporta filtros por status, busca por título e exclusão de filmes.
 */
class AdminMovieList extends Component
{
    use WithPagination;

    /** Filmes exibidos por página. */
    public const PER_PAGE = 30;

    /** Busca por título (?busca=TERMO). */
    #[Url(as: 'busca', except: '')]
    public $busca = '';

    /** Filtro por status1 (?status1=ATIVADO). */
    #[Url(as: 'status1', except: '')]
    public $status1 = '';

    /** Filtro por status2 (?status2=avaliacao). */
    #[Url(as: 'status2', except: '')]
    public $status2 = '';

    /** Mensagem de feedback. */
    public string $mensagem = '';

    public function paginationView()
    {
        return 'pagination::fda';
    }

    public function updatedBusca()
    {
        $this->resetPage();
    }

    public function updatedStatus1()
    {
        $this->resetPage();
    }

    public function updatedStatus2()
    {
        $this->resetPage();
    }

    /**
     * Limpa todos os filtros.
     */
    public function limparFiltros()
    {
        $this->reset('busca', 'status1', 'status2');
        $this->resetPage();
    }

    /**
     * Exclui um filme (apenas o dono pode excluir).
     */
    public function excluir(int $postId): void
    {
        $post = Post::where('id', $postId)->where('user_id', auth()->id())->first();

        if (!$post) {
            $this->mensagem = 'Filme não encontrado ou você não tem permissão.';
            return;
        }

        $post->delete();
        $this->mensagem = '"'.$post->name.'" excluído com sucesso.';
    }

    /**
     * Retorna as opções de status1 disponíveis.
     */
    public function getStatus1Opcoes(): array
    {
        return [
            '' => 'Todos os status',
            'ativado' => 'Ativado',
            'desativado' => 'Desativado',
        ];
    }

    /**
     * Retorna as opções de status2 disponíveis.
     */
    public function getStatus2Opcoes(): array
    {
        return [
            '' => 'Todos',
            'avaliacao' => 'Avaliação',
            'aprovado' => 'Aprovado',
            'rejeitado' => 'Rejeitado',
        ];
    }

    public function render()
    {
        $userId = auth()->id();

        $query = DB::table('categorias_posts')
            ->join('posts', 'categorias_posts.post_id', '=', 'posts.id')
            ->join('categorias', 'categorias_posts.categoria_id', '=', 'categorias.id')
            ->select('posts.id', 'posts.name', 'posts.created_at', 'posts.status1', 'posts.status2', 'categorias.name as categorias')
            ->where('posts.user_id', $userId);

        $termo = trim((string) $this->busca);

        if ($termo !== '') {
            // FULLTEXT em MySQL; fallback para LIKE em SQLite (testes)
            if (config('database.default') === 'sqlite') {
                $query->where('posts.name', 'like', '%'.$termo.'%');
            } else {
                $query->whereRaw('MATCH(posts.name, posts.realname) AGAINST (? IN BOOLEAN MODE)', [$termo]);
            }
        }

        if ($this->status1 !== '') {
            $query->where('posts.status1', $this->status1);
        }

        if ($this->status2 !== '') {
            $query->where('posts.status2', $this->status2);
        }

        $filmes = $query->orderBy('posts.id')->paginate(self::PER_PAGE);

        return view('livewire.admin-movie-list', [
            'filmes' => $filmes,
        ]);
    }
}
