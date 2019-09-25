<?php

namespace App\Http\Controllers;
use App\Categoria;
//use App\Http\Requests\StorePost;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
//use Illuminate\Validation\Validator;
use Intervention\Image\Facades\Image;

class CadController extends Controller
{
    //
    //protected $redirectTo = '/adm/cadastro';



    public function index(Request $request)
    {
        $req_array = $request->all();
        foreach ($req_array as $key =>  $reg ){
            $request->session()->put($key,$reg);
        }
        $request->session()->put('categorias', Categoria::all());

        $request->session()->put('var','teste');

        $paises = Collection::make( json_decode(file_get_contents('./storage/paises-array.json')));
        return view('adm.cadastro',
            [
                'paises' => $paises,
                'categorias' => Categoria::all(),
            ]
        );
        //$callback = $request->name;
    }

    public function store(Request $request){


        //dd($request->capa->getMimeType());


        $paises = Collection::make( json_decode(file_get_contents('./storage/paises-array.json')));
        //$validator = Validator::make($request->all(), $regras, $messages)->validate();
/**
 * Personalizando mensagens
 */
    $messeges=          [
                            'required' => 'O campo :attribute deve ser preenchido',
                            'image' => 'Houve algum problema com a imagem',
                            'max' => 'a :attribute deve ter um tamanho máximo de 5mb ',
                        ];
        /**
         * Tratando erros
         */
    $regras=             [
                            'name' => 'require',
                            'realname' => 'require',
                            'sinopse' => 'require',
                            'diretor' => 'require',
                            'duracao' => 'require',
                            'ano' => 'require',
                            'pais' => 'require',
                            'sinopse' => 'require',
                            'capa' => 'require|image',
                            'label1' => 'require',
                            'url1' => 'require',
                        ];
        $saida = Validator::make($request->all(),$regras,$messeges);

  /*      if ($saida->fails()) {
            return redirect('adm/cadastro')
                ->withErrors($saida)
                ->withInput();
        }
*/
        if($request->tocrop == 1 ){
            $request->session()->put('postdatasave',$request->except('capa') );
            //dd($request->session()->all());
            $upload = $request->capa->storeAs('public/temp', 'tempimage'.Auth::user()->id.'.jpg');
            return view('adm.crop', ['tmpimage' => $upload ]);
        }else{
            return view('adm.cadastro', ['paises' => $paises]);
        }

        }

    protected function  postinsert(Request $request){
        //dd(Collection::make( $request->session()->get('postdatasave'))->all());

        $data = $request->session()->get('postdatasave');
        //dd($data->all());
        //$data = Collection::make($data);
        //dd(  $data['name'] );

        $insert_post_id = DB::table('posts')->insertGetId([
            'name' => $data['name'],
            'realname' => $data['realname'],
            'sinopse' => $data['sinopse'],
            'status1' => 'ativado',
            'status2' => 'avaliacao',
            'user_id' => Auth::user()->id,
            'diretor' => $data['diretor'],
            'duracao' => $data['duracao'],
            'ano' => $data['ano'],
            'pais' => $data['pais'],
        ]);
        //dd($request->all());
        DB::table('categorias_posts')->insert([
            'post_id' => $insert_post_id,
            'categoria_id' => $data['categoria'],
        ]);

        if($data['url1']) {
            DB::table('urls')->insert([
                'post_id' => $insert_post_id,
                'label' => $data['labelurl1'],
                'url' => $data['url1'],
            ]);
        }

        if($data['url2']) {
            DB::table('urls')->insert([
                'post_id' => $insert_post_id,
                'label' => $data['labelurl2'],
                'url' => $data['url2'],
            ]);
        }
        if($data['url3']) {
            DB::table('urls')->insert([
                'post_id' => $insert_post_id,
                'label' => $data['labelurl3'],
                'url' => $data['url3'],
            ]);
        }
        if($data['url4']) {
            DB::table('urls')->insert([
                'post_id' => $insert_post_id,
                'label' => $data['labelurl4'],
                'url' => $data['url4'],
            ]);
        }
        if($data['url5']) {
            DB::table('urls')->insert([
                'post_id' => $insert_post_id,
                'label' => $data['labelurl5'],
                'url' => $data['url5'],
            ]);
        }
        if($data['url6']) {
            DB::table('urls')->insert([
                'post_id' => $insert_post_id,
                'label' => $data['labelurl6'],
                'url' => $data['url6'],
            ]);
        }
        if($request->get('crop')){

            $image_temp = Image::make('storage/temp/tempimage'.Auth::user()->id.'.jpg');
            $image_temp->crop(ceil($request->get('w')),ceil($request->get('h')),$request->get('x'),$request->get('y'));
            $image_temp->save('storage/capas/image'.$insert_post_id.'.jpg');
        }
        $paises = Collection::make( json_decode(file_get_contents('./storage/paises-array.json')));
        return view(
            'adm.cadastro',
            [
            'sucess' => "Cadastro Efetuado con sucesso.",
            'paises' => $paises,
            'categorias' => Categoria::all(),
            ]
        );
    }







}


