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

    public function index (Request $request){
        return view('adm.cad');
    }



}
