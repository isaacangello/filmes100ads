<?php

namespace App\Http\Controllers;

use App\Categoria;
use App\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class PostsController extends Controller
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
                ->select('id_posts','id')
                ->where('id_categorias',"$cate_var")
                ->count();

            $skip_point = ($pag * $exib_regs) - $exib_regs;
            $posts_temp = DB::table('categorias_posts')
                ->select('id_posts','id')
                ->where('id_categorias',"$cate_var")
                ->skip($skip_point)
                ->take($exib_regs)
                ->get();
             $i=0;
             #obtendo nome de categoria
            $categoria_nome = DB::table('categorias')
                ->select('name')
                ->where('id', $cate_var)->first();

            $indices= "([";
            $dados= "([";
            foreach ($posts_temp as $pst_temp)
            {
                //echo $pst_temp;
                if ($i == 0){
                    $indices .= $i;
                }else{
                    $indices .= ','.' '.$i;
                }


                $query_temp = DB::table('posts')
                    ->select('name','sinopse')
                    ->where('id', $pst_temp->id_posts)->first();
                  //echo '<br>'.$pst_temp->id_posts;
                $j=0;
                //echo '  '.$query_temp->name;
                //echo '<br>',$query_temp->sinopse;
                if ($i == 0){
                    $dados .= $query_temp->name;
                    $dados .= ','."\"$query_temp->sinopse\"";
                }else{
                    $dados .= ','.$query_temp->name;
                    $dados .= ','.' '."\"$query_temp->sinopse\"";
                }
                $array_result[$i]["name"] = $query_temp->name;
                $array_result[$i]["sinopse"] = $query_temp->sinopse;
                $i++;
	
            }
            $indices .= "])";
            $dados .= "])";
            //echo $indices;
            //echo $dados;
            $array_result = json_encode($array_result);
            //echo $array_result;
            //$posts = Post::all()->take(60);
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
            $posts = Post::all()->take(60);
    		}
        }
        $total_pages = ceil($total_regs / $exib_regs);

        $cate_var =Input::get('categorias');
        if(!isset($categoria_nome)){$categoria_nome =false;}

        $categorias = Categoria::all();

        return view('posts.index',
            [
                'posts' => $posts,
                'categorias' => $categorias,
                'total_pages' => $total_pages,
                'pagina_atual' => $pag,
                'cate_var' => $cate_var,
                'categoria_nome' => $categoria_nome
            ] );

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
