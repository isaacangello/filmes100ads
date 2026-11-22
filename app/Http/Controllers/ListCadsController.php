<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListCadsController extends Controller
{
    /**
     * Lista dos filmes cadastrados pelo usuário (componente Livewire `AdminMovieList`).
     */
    public function index(Request $request)
    {
        return view('adm.movies.index');
    }
}
