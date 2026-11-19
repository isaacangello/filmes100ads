<?php

namespace App\Http\Controllers;


use App\Models\Categoria;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class IndexController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $array_result = array();
        //

        if(!isset($exib_regs)){
            $exib_regs = 30;
        }

        $total_regs = DB::table('posts')->count();
        //echo 'total de registros-> '.$total_regs;
        $pag = $request->pag;
        if( $request->has('pag') and $request->pag >0  ){$pag = $request->pag;}else{$pag = 1;}
        /*______________________________________________________*/
        /*Verificando a existência da identificação de uma categoria
        *
        */
        if($request->has('categorias'))
        {
            $cate_var =$request->categorias;
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
                    ->select('id','name','sinopse','user_id','ano','duracao')
                    ->where('id', $pst_temp->post_id)->first();
                $array_result[$i] = (object) [
                    "id" => $query_temp->id,
                    "user_id" => $query_temp->user_id,
                    "name" => $query_temp->name,
                    "sinopse" => $query_temp->sinopse,
                    "ano" => $query_temp->ano,
                    "duracao" => $query_temp->duracao,
                ];

                $i++;

            }



            $posts = Collection::make($array_result);
        }else{


            //$posts = Collection::make($posts);
            if ($request->has('pag'))
            {
                /*______________________________________________________*/
                $pag = $request->pag;
                $skip_point = ($pag * $exib_regs) - $exib_regs;
                $posts = DB::table('posts')->select('id','name','sinopse','ano','duracao')
                    ->skip($skip_point)
                    ->take($exib_regs)
                    ->get();
            }
            else
            {
                $posts = DB::table('posts')->select('id','name','sinopse','ano','duracao')
                    ->take($exib_regs)
                    ->get();
            }
        }
        $total_pages = ceil($total_regs / $exib_regs);

        $cate_var =$request->categorias;
        if(!isset($categoria_nome)){$categoria_nome =false;}

        $categorias = Categoria::all();

        /* Categorias dos filmes exibidos (para os chips do card) */
        $categoriaPorPost = [];
        if ($posts && count($posts)) {
            $ids = collect($posts)->pluck('id')->all();
            $categoriaPorPost = DB::table('categorias_posts')
                ->join('categorias', 'categorias_posts.categoria_id', '=', 'categorias.id')
                ->whereIn('categorias_posts.post_id', $ids)
                ->get(['categorias_posts.post_id', 'categorias.name'])
                ->groupBy('post_id')
                ->map(function ($grupo) {
                    return $grupo->first()->name;
                })
                ->toArray();
        }

        /* tratando visualização*/
        //dd($request->filmeid);
            if ($request->filmeid)
            {
                $filmeid = $request->filmeid;
                /* pegando dados do filme*/
                $query_filme = DB::table('posts')
                    ->select('id','name','realname','diretor','pais','descricao','sinopse','duracao','ano')
                    ->where('id', $filmeid)->first();
                $array_filme["id"] = $query_filme->id;
                $array_filme["name"] = $query_filme->name;
                $array_filme["realname"] = $query_filme->realname;
                $array_filme["diretor"] = $query_filme->diretor;
                $array_filme["pais"] = $query_filme->pais;
                $array_filme["descricao"] = $query_filme->descricao;
                $array_filme["sinopse"] = $query_filme->sinopse;
                $array_filme["duracao"] = $query_filme->duracao;
                $array_filme["ano"] = $query_filme->ano;

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
        $dados = [
            'posts' => $posts,
            'categorias' => $categorias,
            'total_pages' => $total_pages,
            'total_regs' => $total_regs,
            'exib_regs' => $exib_regs,
            'pagina_atual' => $pag,
            'pag' => $pag,
            'cate_var' => $cate_var,
            'categoria_nome' => $categoria_nome,
            'visualizar' => $visualizar,
            'urls_visualizar' => $urls_visualizar,
            'categoriaPorPost' => $categoriaPorPost,
        ];

        // Detalhe do filme ou listagem do catálogo.
        return view($visualizar ? 'catalog.show' : 'catalog.index', $dados);

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
