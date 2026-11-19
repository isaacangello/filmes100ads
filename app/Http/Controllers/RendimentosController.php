<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RendimentosController extends Controller
{
    public function index(Request $request)
    {
        return view('adm.earnings');
    }

}
