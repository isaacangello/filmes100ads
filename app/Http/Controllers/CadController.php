<?php

namespace App\Http\Controllers;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Laravel\Facades\Image;

class CadController extends Controller
{
    //
    //protected $redirectTo = '/adm/cadastro';



    public function index(Request $request)
    {
        $paises = $this->paises();

        return view('adm.movies.form', ['paises' => $paises, 'categorias' => $this->categorias()]);
        //$callback = $request->name;
    }


    public function store(Request $request){


        //dd($request->capa->getMimeType());
        $regras =[
            'name' => 'required',
            'realname' => 'required',
            'categoria' => 'required',
            'diretor' => 'required',
            'duracao' => 'required',
            'pais' => 'required',
            'ano' => 'required',
            'sinopse' => 'required',
            'capa' => 'required|image',
            'url1' => 'required',
        ];
        $messages =[
            'required' => 'O campo :attribute deve ser preenchido',
            'image' => 'Houve algum problema com a imagem',
            'max' => 'a :attribute deve ter um tamanho máximo de 5mb ',
        ];

        $paises = $this->paises();
        $validator = Validator::make($request->all(), $regras, $messages)->validate();
        if($request->tocrop == 1 ){
            $request->session()->put('postdatasave',$request->except('capa') );
            //dd($request->session()->all());
            $upload = $request->capa->storeAs('public/temp', 'tempimage'.Auth::user()->id.'.jpg');
            return view('adm.movies.crop', ['tmpimage' => $upload ]);
        }else{
            return view('adm.movies.form', ['paises' => $paises, 'categorias' => $this->categorias()]);
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

        if(!empty($data['url1'])) {
            DB::table('urls')->insert([
                'post_id' => $insert_post_id,
                'label' => $data['labelurl1'],
                'url' => $data['url1'],
            ]);
        }

        if(!empty($data['url2'])) {
            DB::table('urls')->insert([
                'post_id' => $insert_post_id,
                'label' => $data['labelurl2'],
                'url' => $data['url2'],
            ]);
        }
        if(!empty($data['url3'])) {
            DB::table('urls')->insert([
                'post_id' => $insert_post_id,
                'label' => $data['labelurl3'],
                'url' => $data['url3'],
            ]);
        }
        if(!empty($data['url4'])) {
            DB::table('urls')->insert([
                'post_id' => $insert_post_id,
                'label' => $data['labelurl4'],
                'url' => $data['url4'],
            ]);
        }
        if(!empty($data['url5'])) {
            DB::table('urls')->insert([
                'post_id' => $insert_post_id,
                'label' => $data['labelurl5'],
                'url' => $data['url5'],
            ]);
        }
        if(!empty($data['url6'])) {
            DB::table('urls')->insert([
                'post_id' => $insert_post_id,
                'label' => $data['labelurl6'],
                'url' => $data['url6'],
            ]);
        }
        if($request->get('crop')){
            $diretorioCapas = storage_path('app/public/capas');
            if (! is_dir($diretorioCapas)) {
                mkdir($diretorioCapas, 0755, true);
            }

            Image::decodePath(storage_path('app/public/temp/tempimage'.Auth::user()->id.'.jpg'))
                ->crop((int) ceil($request->get('w')), (int) ceil($request->get('h')), (int) $request->get('x'), (int) $request->get('y'))
                ->save($diretorioCapas.'/image'.$insert_post_id.'.jpg');
        }
        $paises = $this->paises();
        return view(
            'adm.movies.form',
            [
                'sucess' => 'Cadastro efetuado com sucesso.',
                'paises' => $paises,
                'categorias' => $this->categorias(),
            ]
        );
    }

    /**
     * Categorias usadas no formulário de cadastro.
     */
    private function categorias(): Collection
    {
        return Categoria::all();
    }

    /**
     * Lista de países usada no formulário de cadastro.
     */
    private function paises(): Collection
    {
        return Collection::make(
            json_decode(file_get_contents(storage_path('app/public/paises-array.json')))
        );
    }







}


