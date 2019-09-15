<?php

namespace App\Http\Controllers;

use App\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AdmController extends Controller
{

    public function index(Request $request)
    {

        $categorias = Categoria::all();
        Session::put('caegorias', $categorias);
        return view('adm.adm');

    }



}

