<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CadController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function index (){
        echo 'aqui';
        return view('adm.cad');
    }
    public function show (Request $request){
        echo 'aqui';
        redirect('cadastro.index');
    }



}
