<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class listcadsController extends Controller
{
    //
    public function queryposts($skip_point,$exib_regs){
        $posts_user = DB::table('categorias_posts')
            ->join('posts','categorias_posts.post_id','=','posts.id')
            ->join('categorias','categorias_posts.categoria_id','=','categorias.id')
            ->select('posts.id','posts.name','posts.status1 as status','posts.realname','categorias.name as categorias')
            ->where('user_id',Auth::user()->id)
            ->skip($skip_point)
            ->take($exib_regs)
            ->get();
        return $posts_user;
    }
    public function index(Request $request,$pag = 1){
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

/*
        $posts_user = DB::table('categorias_posts')
            ->join('posts','categorias_posts.post_id','=','posts.id')
            ->join('categorias','categorias_posts.categoria_id','=','categorias.id')
            ->select('posts.id','posts.name','posts.realname','categorias.name as categorias')
            ->where('user_id',Auth::user()->id)
            ->paginate($exib_regs);

*/
        $posts_user = $this->queryposts($skip_point,$exib_regs);
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
    public function modstatus (Request $request)
    {

        if ($request->excluir && $request->excluir > 0) {

            DB::table('posts')
                ->where('id', $request->excluir)
                ->update(['status1'=> 'desativado']);
            //dd(Collection::make(json_decode($request->posts_user,true,512,JSON_OBJECT_AS_ARRAY)) );
            if(!isset($exib_regs)){$exib_regs = 30;}
            if(isset($request['pag'])){$pag = $request['pag'];}else{$pag = 1;}
            $skip_point = ($request->pag * $request->exib_regs) - $request->exib_regs;
            return view('adm.listacadastro',
                [
                    'posts_user' => $this->queryposts($skip_point,$request->exib_regs) ,
                    'pag' => $request->pag,
                    'total_regs' => $request->total_regs,
                    'exib_regs' => $request->exib_regs
                ]
            );


        }
    }
}

