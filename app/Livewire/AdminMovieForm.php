<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Formulário de cadastro e edição de filme (área ADM).
 *
 * Novos filmes e capas substituídas passam pela etapa de recorte. Na edição,
 * quando a capa atual é mantida, os dados são atualizados imediatamente.
 */
class AdminMovieForm extends Component
{
    use WithFileUploads;

    public ?Post $post = null;

    /**
     * Carrega o filme no modo de edição e impede acesso a filmes de terceiros.
     */
    public function mount($post = null): void
    {
        if (! $post) {
            return;
        }

        if (! $post instanceof Post) {
            $post = Post::findOrFail($post);
        }

        abort_unless((int) $post->user_id === auth()->id(), 403);

        $this->post = $post->load(['categorias']);
        $this->name = $post->name;
        $this->realname = $post->realname;
        $this->categoria = (string) ($post->categorias->first()?->id ?? '');
        $this->sinopse = $post->sinopse;
        $this->diretor = $post->diretor;
        $this->duracao = $post->duracao;
        $this->ano = $post->ano;
        $this->pais = $post->pais;
    }

    /* ------------------------------------------------------------------ */
    /* Dados do filme                                                     */
    /* ------------------------------------------------------------------ */
    public $name = '';
    public $realname = '';
    public $categoria = '';
    public $sinopse = '';
    public $diretor = '';
    public $duracao = '';
    public $ano = '';
    public $pais = '';

    /* ------------------------------------------------------------------ */
    /* Capa                                                               */
    /* ------------------------------------------------------------------ */
    public $capa;

    /**
     * Campos que vão para a sessão e, depois, para o banco.
     */
    private const CAMPOS = [
        'name', 'realname', 'categoria', 'sinopse', 'diretor', 'duracao', 'ano', 'pais',
    ];

    /**
     * Regras de validação do formulário.
     */
    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'realname' => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'exists:categorias,id'],
            'sinopse' => ['required', 'string'],
            'diretor' => ['required', 'string', 'max:255'],
            'duracao' => ['required', 'integer', 'min:1', 'max:600'],
            'ano' => ['required', 'integer', 'min:1910', 'max:'.(date('Y') + 1)],
            'pais' => ['required', 'string', 'max:100'],
            'capa' => [$this->post ? 'nullable' : 'required', 'image', 'max:5120'],
        ];
    }

    /**
     * Mensagens de erro em português.
     */
    protected function validationAttributes()
    {
        return [
            'name' => 'título do filme',
            'realname' => 'título original',
            'categoria' => 'categoria',
            'sinopse' => 'sinopse',
            'diretor' => 'diretor',
            'duracao' => 'duração',
            'ano' => 'ano',
            'pais' => 'país de origem',
            'capa' => 'capa',
        ];
    }

    /**
     * Salva a edição ou guarda os dados na sessão para o recorte da capa.
     */
    public function save()
    {
        $this->validate();

        $data = $this->only(self::CAMPOS);

        if ($this->post) {
            $post = Post::find($this->post->id);
            abort_unless($post && (int) $post->user_id === auth()->id(), 403);
            $this->post = $post;

            if (! $this->capa) {
                $this->updatePost($data);
                session()->forget('postdatasave');

                return redirect()
                    ->route('adm')
                    ->with('sucess', 'Filme atualizado com sucesso!');
            }

            $data['post_id'] = $post->id;
        }

        session(['postdatasave' => $data]);

        // Guarda a imagem enviada para o recorte (storage/app/public/temp).
        $this->capa->storeAs('temp', 'tempimage'.auth()->id().'.jpg', 'public');

        return redirect()->route('adm.movies.crop');
    }

    /**
     * Atualiza o filme sem substituir a capa atual.
     */
    private function updatePost(array $data): void
    {
        DB::transaction(function () use ($data) {
            $this->post->update([
                'name' => $data['name'],
                'realname' => $data['realname'],
                'sinopse' => $data['sinopse'],
                'diretor' => $data['diretor'],
                'duracao' => $data['duracao'],
                'ano' => $data['ano'],
                'pais' => $data['pais'],
            ]);

            $this->post->categorias()->sync([$data['categoria']]);
        });
    }

    /**
     * Lista de países usada no formulário.
     */
    private function paises(): Collection
    {
        $arquivo = storage_path('app/public/paises-array.json');

        if (! is_file($arquivo)) {
            return collect();
        }

        return Collection::make(json_decode(file_get_contents($arquivo)));
    }

    public function render()
    {
        return view('livewire.admin-movie-form', [
            'categorias' => Categoria::orderBy('name')->get(),
            'paises' => $this->paises(),
        ]);
    }
}
