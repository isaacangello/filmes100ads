<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdmController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $viewchar = $request->viewchar;
        echo $request->viewchar;
        if ($viewchar) {
            switch ($viewchar) {
                case 'cad':
                    echo 'aqui';
                    return view('adm.cadastro');
                    break;
                case 1:
                    echo "i equals 1";
                    break;
                case 2:
                    echo "i equals 2";
                    break;
                default:
                    ;
            }
        }else

            return view('adm.adm');
        }



    public function show(Request $request){
        $viewchar = $request->viewchar;
        echo $request->viewchar;
        if ($viewchar) {
            switch ($viewchar) {
                case 'cad':
                    return view('adm.cadastro');
                    break;
                case 1:
                    echo "i equals 1";
                    break;
                case 2:
                    echo "i equals 2";
                    break;
                default:
                    ;
            }
                return view('adm.rendimentos');
        }




    }

}

