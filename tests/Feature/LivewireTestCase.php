<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LivewireTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * Cria um usuário de teste.
     */
    protected function criarUsuario(string $email = 'teste@fda.local'): User
    {
        return User::create([
            'name' => 'Teste',
            'email' => $email,
            'password' => bcrypt('password123'),
        ]);
    }

    /**
     * Cria categorias e filmes para os testes.
     *
     * @return array{0: Categoria, 1: Categoria, 2: Post, 3: Post}
     */
    protected function criarCatalogo(): array
    {
        $acao = Categoria::create(['name' => 'Ação', 'status1' => 'ativo']);
        $drama = Categoria::create(['name' => 'Drama', 'status1' => 'ativo']);

        $user = $this->criarUsuario();
        $outro = $this->criarUsuario('outro@fda.local');

        $matrix = Post::create([
            'user_id' => $user->id,
            'name' => 'Matrix',
            'realname' => 'The Matrix',
            'sinopse' => 'Um hacker descobre a realidade.',
            'diretor' => 'Wachowski',
            'duracao' => 136,
            'ano' => 1999,
            'pais' => 'Estados Unidos',
            'status1' => 'ativado',
            'status2' => 'avaliacao',
            'magnet_link' => 'magnet:?xt=urn:btih:test123',
        ]);

        $avatar = Post::create([
            'user_id' => $outro->id,
            'name' => 'Avatar',
            'realname' => 'Avatar',
            'sinopse' => 'Um alienígena no planeta Pandora.',
            'diretor' => 'Cameron',
            'duracao' => 162,
            'ano' => 2009,
            'pais' => 'Estados Unidos',
            'status1' => 'ativado',
            'status2' => 'avaliacao',
        ]);

        \DB::table('categorias_posts')->insert([
            ['post_id' => $matrix->id, 'categoria_id' => $acao->id],
            ['post_id' => $avatar->id, 'categoria_id' => $drama->id],
        ]);

        return [$acao, $drama, $matrix, $avatar];
    }
}
