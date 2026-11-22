<?php

namespace App\Http\Controllers;

use App\Models\Post;

/**
 * Controller simples que apenas renderiza as páginas de torrents.
 *
 * A lógica de busca é delegada ao componente Livewire `TorrentSearch`.
 */
class TorrentController extends Controller
{
    /**
     * Página de busca de torrents.
     */
    public function index()
    {
        return view('torrents.index');
    }

    /**
     * Redireciona para a página de detalhe do filme com a seção de torrents.
     *
     * A busca de torrents agora é feita inline no componente MovieDetail,
     * então esta rota apenas redireciona para a página do filme.
     */
    public function movie(Post $post)
    {
        return redirect()->to(route('index', ['filmeid' => $post->id]) . '#torrents');
    }
}
