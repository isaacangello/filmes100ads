<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

//use Symfony\Component\Routing\Route;

class AdmController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        return view('adm.adm');
    }

    public function show(){
        if (Input::get('viewchar'))
        {
            $viewchar = Input::get('viewchar');
            switch ($viewchar) {
                case 'cad':
                    return redirect('adm/cad');
                    break;
                case 1:
                    echo "i equals 1";
                    break;
                case 2:
                    echo "i equals 2";
                    break;
                default:
                    return redirect('adm');
            }


        }

    }


}
