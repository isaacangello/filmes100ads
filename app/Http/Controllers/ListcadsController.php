<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class listcadsController extends Controller
{
    //
    public function index(Request $request){
        /*-------------------------------------------------------------------------------------------------*/
        /*Tratando quantidade de registros por página.
        /*-------------------------------------------------------------------------------------------------*/
        if(!isset($exib_regs)){
            $exib_regs = 30;
        }
        /*-------------------------------------------------------------------------------------------------*/
        /*Tratando quantidade total de registros.
        /*-------------------------------------------------------------------------------------------------*/
        $total_regs = DB::table('posts')
        ->select('id','name','realname')
        ->where('user_id',Auth::user()->id)
        ->count();

        /*--------------------------------------*/
        /*Tratando dados de entrada e saida.
        /*--------------------------------------*/

        if(isset($request['pag'])){
            $pag = $request['pag'];

        }
        else
        {
            $pag = 1;

        }
        //dd($request['pag']);
        $skip_point = ($pag * $exib_regs) - $exib_regs;

        $posts_user = DB::table('categorias_posts')
            ->join('posts','categorias_posts.post_id','=','posts.id')
            ->join('categorias','categorias_posts.categoria_id','=','categorias.id')
            ->select('posts.id','posts.name','posts.realname','categorias.name as categorias')
            ->where('user_id',Auth::user()->id)
            ->skip($skip_point)
            ->take($exib_regs)
            ->get();
/*
        $posts_user = DB::table('categorias_posts')
            ->join('posts','categorias_posts.post_id','=','posts.id')
            ->join('categorias','categorias_posts.categoria_id','=','categorias.id')
            ->select('posts.id','posts.name','posts.realname','categorias.name as categorias')
            ->where('user_id',Auth::user()->id)
            ->paginate($exib_regs);
*/
        //dd($posts_user);
        return view ('adm.listacadastro',
        [
            'posts_user' => $posts_user,
            'pag' => $pag,
            'total_regs' => $total_regs,
            'exib_regs' => $exib_regs
        ]
        );
    }
}
