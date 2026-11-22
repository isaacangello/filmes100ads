<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Post;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    /**
     * Catálogo público (?categorias=ID&pag=N) ou detalhe do filme (?filmeid=ID).
     *
     * A listagem e o detalhe são componentes Livewire:
     * `MovieCatalog` e `MovieDetail`.
     */
    public function index(Request $request)
    {
        /* Detalhe do filme. */
        if ($request->filled('filmeid')) {
            $filme = Post::find((int) $request->filmeid);

            abort_if(! $filme, 404, 'Filme não encontrado.');

            return view('catalog.show', [
                'filmeid' => $filme->id,
                'filmeNome' => $filme->name,
            ]);
        }

        /* Catálogo. */
        return view('catalog.index', [
            'totalFilmes' => Post::count(),
            'totalCategorias' => Categoria::count(),
        ]);
    }
}
