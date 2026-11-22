<?php

namespace Tests\Feature;

use App\Livewire\MovieCatalog;
use App\Livewire\MovieDetail;
use App\Models\Post;
use Livewire\Livewire;

class CatalogTest extends LivewireTestCase
{
    public function test_a_home_lista_o_catalogo_publico(): void
    {
        $this->criarCatalogo();

        $this->get('/')
            ->assertOk()
            ->assertSee('Catálogo completo')
            ->assertSee('Matrix')
            ->assertSee('Avatar');
    }

    public function test_a_home_filtra_pela_categoria_da_url(): void
    {
        [$acao] = $this->criarCatalogo();

        $this->get('/?categorias='.$acao->id)
            ->assertOk()
            ->assertSee('Matrix')
            ->assertDontSee('Avatar');
    }

    public function test_o_componente_filtra_por_categoria(): void
    {
        [$acao] = $this->criarCatalogo();

        Livewire::test(MovieCatalog::class)
            ->assertSee('Matrix')
            ->assertSee('Avatar')
            ->set('categoriaId', (string) $acao->id)
            ->assertSee('Matrix')
            ->assertDontSee('Avatar');
    }

    public function test_o_componente_busca_pelo_titulo(): void
    {
        $this->criarCatalogo();

        Livewire::test(MovieCatalog::class)
            ->set('busca', 'Avatar')
            ->assertSee('Avatar')
            ->assertDontSee('Matrix');
    }

    public function test_o_componente_pagina_os_filmes(): void
    {
        [$acao] = $this->criarCatalogo();

        // Filmes extras para forçar mais de uma página.
        for ($i = 1; $i <= 35; $i++) {
            Post::create([
                'user_id' => $acao->id,
                'name' => 'Paging '.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'status1' => 'ativado',
                'status2' => 'avaliacao',
            ]);
        }

        Livewire::test(MovieCatalog::class)
            ->assertSee('Matrix')
            ->call('gotoPage', 2)
            ->assertSee('Paging 31')
            ->assertDontSee('Matrix');
    }

    public function test_a_pagina_de_detalhe_mostra_o_filme(): void
    {
        [, , $matrix] = $this->criarCatalogo();

        $this->get('/?filmeid='.$matrix->id)
            ->assertOk()
            ->assertSee('Matrix')
            ->assertSee('Download do Filme');
    }

    public function test_o_componente_de_detalhe_carrega_o_filme(): void
    {
        [, , $matrix] = $this->criarCatalogo();

        Livewire::test(MovieDetail::class, ['filmeid' => $matrix->id])
            ->assertOk()
            ->assertSee('Matrix')
            ->assertSee('Ação');
    }

    public function test_filme_inexistente_retorna_404(): void
    {
        $this->criarCatalogo();

        $this->get('/?filmeid=9999')->assertNotFound();
    }
}
