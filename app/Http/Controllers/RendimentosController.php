<?php

namespace App\Http\Controllers;

/**
 * Controller da tela de rendimentos.
 *
 * A lógica de dados é delegada ao componente Livewire `EarningsDashboard`.
 */
class RendimentosController extends Controller
{
    /**
     * Página de rendimentos do usuário.
     */
    public function index()
    {
        return view('adm.earnings');
    }
}
