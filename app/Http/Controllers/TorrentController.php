<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TorrentController extends Controller
{
    /**
     * Página de busca de torrents.
     *
     * Na Fase 2 esta rota passa a ser alimentada pelo componente Livewire
     * `TorrentSearch`, que consulta os provedores públicos configurados.
     */
    public function index(Request $request)
    {
        $termo = trim((string) $request->query('q', ''));

        return view('torrents.index', [
            'termo' => $termo,
        ]);
    }
}
