<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller para gerência de usuários na área ADM.
 *
 * Apenas admins podem criar, editar e excluir usuários.
 */
class AdmUserController extends Controller
{
    /**
     * Exibe a lista de usuários (renderizada pelo componente Livewire UserManager).
     */
    public function index(): View
    {
        return view('adm.users.index');
    }

    /**
     * Exibe o formulário de criação de usuário.
     */
    public function create(): View
    {
        return view('adm.users.create');
    }

    /**
     * Salva um novo usuário.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'is_admin' => ['boolean'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'is_admin' => $request->has('is_admin'),
        ]);

        return redirect()->route('adm.users.index')->with('success', 'Usuário criado com sucesso.');
    }

    /**
     * Atualiza um usuário existente.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'is_admin' => ['boolean'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_admin' => $request->has('is_admin'),
        ]);

        return redirect()->route('adm.users.index')->with('success', 'Usuário atualizado com sucesso.');
    }

    /**
     * Exclui um usuário.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Impede que o admin exclua a si mesmo
        if ($user->id === auth()->id()) {
            return redirect()->route('adm.users.index')->with('error', 'Você não pode excluir a si mesmo.');
        }

        $user->delete();

        return redirect()->route('adm.users.index')->with('success', 'Usuário excluído com sucesso.');
    }
}
