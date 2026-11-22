<?php

namespace Tests\Feature;

use App\Livewire\AdminMovieForm;
use App\Livewire\AdminMovieList;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

class AdminMoviesTest extends LivewireTestCase
{
    public function test_a_area_adm_exige_login(): void
    {
        $this->get('/adm/cadastro')->assertRedirect('/login');
        $this->get('/adm/meuscadastros')->assertRedirect('/login');
    }

    public function test_o_formulario_exibe_as_categorias_e_os_paises(): void
    {
        $user = $this->criarUsuario();
        $this->actingAs($user);

        $this->get('/adm/cadastro')
            ->assertOk()
            ->assertSee('Cadastrar filme');
    }

    public function test_o_formulario_valida_os_campos_obrigatorios(): void
    {
        $user = $this->criarUsuario();
        $this->actingAs($user);

        Livewire::test(AdminMovieForm::class)
            ->call('save')
            ->assertHasErrors(['name', 'realname', 'categoria', 'sinopse', 'diretor', 'duracao', 'ano', 'pais', 'capa']);
    }

    public function test_o_formulario_salva_os_dados_e_vai_para_o_recorte(): void
    {
        Storage::fake('public');

        $user = $this->criarUsuario();
        $this->actingAs($user);

        $categoria = \App\Models\Categoria::create(['name' => 'Ficção', 'status1' => 'ativo']);

        Livewire::test(AdminMovieForm::class)
            ->set('name', 'Interestelaris')
            ->set('realname', 'Interstellar')
            ->set('categoria', (string) $categoria->id)
            ->set('sinopse', 'Um grupo de exploradores viaja pelo espaço.')
            ->set('diretor', 'Christopher Nolan')
            ->set('duracao', 169)
            ->set('ano', 2014)
            ->set('pais', 'Estados Unidos')
            ->set('capa', UploadedFile::fake()->image('capa.jpg', 800, 1200))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('adm.movies.crop'));

        $dados = session('postdatasave');

        $this->assertSame('Interestelaris', $dados['name']);
        $this->assertSame('Interstellar', $dados['realname']);
        $this->assertSame('Christopher Nolan', $dados['diretor']);

        Storage::disk('public')->assertExists('temp/tempimage'.$user->id.'.jpg');

        // A página de recorte precisa estar disponível logo após o envio.
        $this->get(route('adm.movies.crop'))
            ->assertOk()
            ->assertSee('croppie');
    }

    public function test_a_lista_mostra_apenas_os_filmes_do_usuario(): void
    {
        $this->criarCatalogo();

        $user = $this->criarUsuario('dono@fda.local');

        Livewire::test(AdminMovieList::class, [])
            ->assertOk();

        // O usuário da sessão é quem define o filtro.
        $this->actingAs($user);

        Livewire::test(AdminMovieList::class)
            ->assertSee('Filmes cadastrados')
            ->assertDontSee('Matrix');
    }

    public function test_admin_movie_list_filters_by_status(): void
    {
        $this->criarCatalogo();
        $user = \App\Models\User::where('email', 'teste@fda.local')->first();
        $this->actingAs($user);

        Livewire::test(AdminMovieList::class)
            ->set('status1', 'ativado')
            ->assertSee('Matrix');
    }

    public function test_user_can_delete_their_own_movie(): void
    {
        [, , $matrix] = $this->criarCatalogo();
        $user = \App\Models\User::where('email', 'teste@fda.local')->first();
        $this->actingAs($user);

        Livewire::test(AdminMovieList::class)
            ->call('excluir', $matrix->id)
            ->assertSet('mensagem', '"Matrix" excluído com sucesso.');

        $this->assertDatabaseMissing('posts', ['id' => $matrix->id]);
    }

    public function test_user_cannot_delete_another_users_movie(): void
    {
        [, , , $avatar] = $this->criarCatalogo();
        // Avatar pertence a outro@fda.local
        $user = $this->criarUsuario('dono@fda.local');
        $this->actingAs($user);

        Livewire::test(AdminMovieList::class)
            ->call('excluir', $avatar->id)
            ->assertSet('mensagem', 'Filme não encontrado ou você não tem permissão.');

        $this->assertDatabaseHas('posts', ['id' => $avatar->id]);
    }

    public function test_edit_route_requires_ownership(): void
    {
        [, , $matrix] = $this->criarCatalogo();
        // Matrix pertence a teste@fda.local, outro@fda.local não pode editar
        $user = \App\Models\User::where('email', 'outro@fda.local')->first();
        $this->actingAs($user);

        $this->get(route('adm.movies.edit', $matrix->id))
            ->assertForbidden();
    }

    public function test_edit_route_loads_for_owner(): void
    {
        [, , $matrix] = $this->criarCatalogo();
        $user = \App\Models\User::where('email', 'teste@fda.local')->first();
        $this->actingAs($user);

        $this->get(route('adm.movies.edit', $matrix->id))
            ->assertOk()
            ->assertSee('Matrix');
    }

    public function test_edit_form_loads_movie_data(): void
    {
        [$acao, , $matrix] = $this->criarCatalogo();
        $user = \App\Models\User::where('email', 'teste@fda.local')->first();
        $this->actingAs($user);

        Livewire::test(AdminMovieForm::class, ['post' => $matrix])
            ->assertSet('name', 'Matrix')
            ->assertSet('realname', 'The Matrix')
            ->assertSet('categoria', (string) $acao->id)
            ->assertSet('sinopse', 'Um hacker descobre a realidade.')
            ->assertSet('diretor', 'Wachowski')
            ->assertSet('duracao', 136)
            ->assertSet('ano', 1999)
            ->assertSet('pais', 'Estados Unidos');
    }

    public function test_owner_can_update_movie_without_replacing_cover(): void
    {
        [$acao, $drama, $matrix] = $this->criarCatalogo();
        $user = \App\Models\User::where('email', 'teste@fda.local')->first();
        $this->actingAs($user);

        Livewire::test(AdminMovieForm::class, ['post' => $matrix])
            ->set('name', 'Matrix Reloaded')
            ->set('categoria', (string) $drama->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('adm'));

        $this->assertDatabaseHas('posts', [
            'id' => $matrix->id,
            'name' => 'Matrix Reloaded',
            'user_id' => $user->id,
            'status1' => 'ativado',
            'status2' => 'avaliacao',
        ]);
        $this->assertDatabaseHas('categorias_posts', [
            'post_id' => $matrix->id,
            'categoria_id' => $drama->id,
        ]);
        $this->assertDatabaseMissing('categorias_posts', [
            'post_id' => $matrix->id,
            'categoria_id' => $acao->id,
        ]);
        $this->assertDatabaseCount('posts', 2);
    }

    public function test_owner_can_update_movie_and_replace_cover(): void
    {
        Storage::fake('public');

        [, $drama, $matrix] = $this->criarCatalogo();
        $user = \App\Models\User::where('email', 'teste@fda.local')->first();
        $this->actingAs($user);

        Livewire::test(AdminMovieForm::class, ['post' => $matrix])
            ->set('name', 'Matrix Resurrections')
            ->set('categoria', (string) $drama->id)
            ->set('capa', UploadedFile::fake()->image('nova-capa.jpg', 800, 1200))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('adm.movies.crop'));

        $this->assertSame($matrix->id, session('postdatasave.post_id'));

        $this->post(route('cadastro.salvando'), [
            'crop' => 1,
            'x' => 0,
            'y' => 0,
            'w' => 400,
            'h' => 600,
        ])->assertRedirect(route('adm'));

        $this->assertDatabaseHas('posts', [
            'id' => $matrix->id,
            'name' => 'Matrix Resurrections',
        ]);
        $this->assertDatabaseHas('categorias_posts', [
            'post_id' => $matrix->id,
            'categoria_id' => $drama->id,
        ]);
        $this->assertDatabaseCount('posts', 2);
        Storage::disk('public')->assertExists('capas/image'.$matrix->id.'.jpg');
    }

    public function test_user_cannot_mount_edit_form_for_another_users_movie(): void
    {
        [, , , $avatar] = $this->criarCatalogo();
        $user = \App\Models\User::where('email', 'teste@fda.local')->first();
        $this->actingAs($user);

        Livewire::test(AdminMovieForm::class, ['post' => $avatar])
            ->assertForbidden();
    }
}
