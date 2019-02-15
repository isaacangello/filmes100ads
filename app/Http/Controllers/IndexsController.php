<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Categoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Collection;

class IndexsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $array_result = array();
        //

        if(!isset($exib_regs)){
            $exib_regs = 60;
        }

        $total_regs = DB::table('posts')->count();
        //echo 'total de registros-> '.$total_regs;
        if(Input::get('pag')){$pag = Input::get('pag');}else{$pag = 1;}
        /*______________________________________________________*/
        /*Verificando a existência da identificação de uma categoria
        *
        */
        if(Input::get('categorias'))
        {
            $cate_var =Input::get('categorias');
            $total_regs = DB::table('categorias_posts')
                ->select('post_id','id')
                ->where('categoria_id',"$cate_var")
                ->count();

            $skip_point = ($pag * $exib_regs) - $exib_regs;
            $posts_temp = DB::table('categorias_posts')
                ->select('post_id','id')
                ->where('categoria_id',"$cate_var")
                ->skip($skip_point)
                ->take($exib_regs)
                ->get();
            $i=0;
            #obtendo nome de categoria
            $categoria_nome = DB::table('categorias')
                ->select('name')
                ->where('id', $cate_var)->first();

            foreach ($posts_temp as $pst_temp)
            {

                $query_temp = DB::table('posts')
                    ->select('id','name','sinopse')
                    ->where('id', $pst_temp->post_id)->first();
                $array_result[$i]["id"] = $query_temp->id;
                $array_result[$i]["name"] = $query_temp->name;
                $array_result[$i]["sinopse"] = $query_temp->sinopse;

                $i++;

            }
            $array_result = json_encode($array_result);


            $posts = Collection::make(json_decode($array_result));
        }else{


            //$posts = Collection::make($posts);
            if (Input::get('pag'))
            {
                /*______________________________________________________*/
                $pag = Input::get('pag');
                $skip_point = ($pag * $exib_regs) - $exib_regs;
                $posts = DB::table('posts')->select('id','name','sinopse')
                    ->skip($skip_point)
                    ->take($exib_regs)
                    ->get();
            }

            else
            {
                $posts = Post::all()->take($exib_regs);
            }
        }
        $total_pages = ceil($total_regs / $exib_regs);

        $cate_var =Input::get('categorias');
        if(!isset($categoria_nome)){$categoria_nome =false;}

        $categorias = Categoria::all();
        /* tratando visualização*/
            if (Input::get('filmeid'))
            {
                $filmeid = Input::get('filmeid');
                /* pegando dados do filme*/
                $query_filme = DB::table('posts')
                    ->select('id','name','sinopse')
                    ->where('id', $filmeid)->first();
                $array_filme["id"] = $query_filme->id;
                $array_filme["name"] = $query_filme->name;
                $array_filme["sinopse"] = $query_filme->sinopse;

                $urls_visualizar = DB::table('urls')
                    ->select('label','url')
                    ->where('post_id',$filmeid)->get();
                //echo $array_filme['name'];
                /* pegando categoria*/
                $cate_var1 = DB::table('categorias_posts')
                    ->select('categoria_id','id')
                    ->where('post_id',$filmeid)
                    ->first();

            $categoria_filme_nome = DB::table('categorias')
                ->select('name')
                ->where('id', $cate_var1->categoria_id)->first();
            $array_filme["categoria"] = $categoria_filme_nome->name;
            $visualizar = Collection::make($array_filme);
             //echo    $visualizar;
            }else
            {
                $visualizar = false;
                $urls_visualizar = false;
            }
        return view
        (
            'posts.index',
            [
                'posts' => $posts,
                'categorias' => $categorias,
                'total_pages' => $total_pages,
                'pagina_atual' => $pag,
                'cate_var' => $cate_var,
                'categoria_nome' => $categoria_nome,
                'visualizar' => $visualizar,
                'urls_visualizar' => $urls_visualizar

            ]
        );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
