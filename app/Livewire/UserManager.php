<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Componente Livewire para gerenciar usuários na área ADM.
 *
 * Lista todos os usuários com paginação, busca por nome/email,
 * e ações de toggle admin e exclusão.
 */
class UserManager extends Component
{
    use WithPagination;

    /** Usuários exibidos por página. */
    public const PER_PAGE = 30;

    /** Busca por nome ou email (?busca=TERMO). */
    #[Url(as: 'busca', except: '')]
    public $busca = '';

    /** Mensagem de feedback. */
    public string $mensagem = '';

    public function updatedBusca()
    {
        $this->resetPage();
    }

    /**
     * Alterna o status de admin de um usuário.
     */
    public function toggleAdmin(int $userId): void
    {
        $user = User::find($userId);

        if (!$user) {
            $this->mensagem = 'Usuário não encontrado.';
            return;
        }

        // Impede que o admin remova o próprio status de admin
        if ($user->id === auth()->id()) {
            $this->mensagem = 'Você não pode remover o seu próprio status de admin.';
            return;
        }

        $user->update(['is_admin' => ! $user->is_admin]);
        $this->mensagem = 'Status de admin atualizado para "'.$user->name.'".';
    }

    /**
     * Exclui um usuário (não pode excluir a si mesmo).
     */
    public function excluir(int $userId): void
    {
        $user = User::find($userId);

        if (!$user) {
            $this->mensagem = 'Usuário não encontrado.';
            return;
        }

        // Impede que o admin exclua a si mesmo
        if ($user->id === auth()->id()) {
            $this->mensagem = 'Você não pode excluir a si mesmo.';
            return;
        }

        $user->delete();
        $this->mensagem = '"'.$user->name.'" excluído com sucesso.';
    }

    public function render()
    {
        $query = User::query()
            ->select('id', 'name', 'email', 'is_admin', 'created_at')
            ->orderBy('id');

        $termo = trim((string) $this->busca);

        if ($termo !== '') {
            // FULLTEXT em MySQL; fallback para LIKE em SQLite (testes)
            if (config('database.default') === 'sqlite') {
                $query->where(function ($q) use ($termo) {
                    $q->where('name', 'like', '%'.$termo.'%')
                      ->orWhere('email', 'like', '%'.$termo.'%');
                });
            } else {
                $query->whereRaw('MATCH(name, email) AGAINST (? IN BOOLEAN MODE)', [$termo]);
            }
        }

        $usuarios = $query->paginate(self::PER_PAGE);

        return view('livewire.user-manager', [
            'usuarios' => $usuarios,
        ]);
    }
}
