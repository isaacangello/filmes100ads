<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class CadController extends Controller
{
    /**
     * Formulário de cadastro de filme (componente Livewire `AdminMovieForm`).
     */
    public function index(Request $request)
    {
        return view('adm.movies.form');
    }

    /**
     * Formulário de edição de filme existente.
     */
    public function edit(Post $post)
    {
        // Apenas o dono pode editar
        if ($post->user_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para editar este filme.');
        }

        return view('adm.movies.form', ['post' => $post]);
    }

    /**
     * Etapa de recorte da capa.
     *
     * Os dados do filme ficam na sessão (enviados pelo componente Livewire)
     * e a imagem temporária em `storage/app/public/temp`.
     */
    public function crop(Request $request)
    {
        if (! $request->session()->has('postdatasave')) {
            return redirect()
                ->route('cadastro.index')
                ->withErrors(['capa' => 'A imagem temporária expirou. Envie a capa novamente.']);
        }

        return view('adm.movies.crop');
    }

    /**
     * Gravação final do filme (após o recorte da capa).
     */
    protected function postinsert(Request $request)
    {
        $data = $request->session()->get('postdatasave');

        if (! $data) {
            return redirect()
                ->route('cadastro.index')
                ->withErrors(['capa' => 'Os dados do cadastro expiraram. Envie o formulário novamente.']);
        }

        $post = null;

        if (! empty($data['post_id'])) {
            $post = Post::find($data['post_id']);
            abort_unless($post && (int) $post->user_id === Auth::id(), 403);
        }

        $insert_post_id = DB::transaction(function () use ($data, $post) {
            if ($post) {
                $post->update([
                    'name' => $data['name'],
                    'realname' => $data['realname'],
                    'sinopse' => $data['sinopse'],
                    'diretor' => $data['diretor'],
                    'duracao' => $data['duracao'],
                    'ano' => $data['ano'],
                    'pais' => $data['pais'],
                ]);
                $post->categorias()->sync([$data['categoria']]);

                return $post->id;
            }

            $postId = DB::table('posts')->insertGetId([
                'name' => $data['name'],
                'realname' => $data['realname'],
                'sinopse' => $data['sinopse'],
                'status1' => 'ativado',
                'status2' => 'avaliacao',
                'user_id' => Auth::id(),
                'diretor' => $data['diretor'],
                'duracao' => $data['duracao'],
                'ano' => $data['ano'],
                'pais' => $data['pais'],
            ]);

            DB::table('categorias_posts')->insert([
                'post_id' => $postId,
                'categoria_id' => $data['categoria'],
            ]);

            return $postId;
        });

        /* Recorte da capa. */
        if ($request->get('crop')) {
            $diretorioCapas = Storage::disk('public')->path('capas');

            if (! is_dir($diretorioCapas)) {
                mkdir($diretorioCapas, 0755, true);
            }

            Image::decodePath(Storage::disk('public')->path('temp/tempimage'.Auth::id().'.jpg'))
                ->crop((int) ceil($request->get('w')), (int) ceil($request->get('h')), (int) $request->get('x'), (int) $request->get('y'))
                ->save($diretorioCapas.'/image'.$insert_post_id.'.jpg');
        }

        /* Limpa os dados temporários da sessão. */
        $request->session()->forget('postdatasave');

        return redirect()
            ->route('adm')
            ->with('sucess', $post ? 'Filme atualizado com sucesso!' : 'Filme cadastrado com sucesso!');
    }
}
