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

    public function test_a_home_exibe_aviso_e_opcoes_independentes_para_mineracao(): void
    {
        $this->criarCatalogo();
        config()->set('miner.enabled', true);
        config()->set('miner.site_key', 'test-site-key');
        config()->set('miner.script_path', 'https://www.hostingcloud.racing/RqH3.js');

        $this->get('/')
            ->assertOk()
            ->assertSee('Antes de entrar')
            ->assertSee('O acesso à página inicial requer autorização', false)
            ->assertSee('Aceitar e entrar')
            ->assertDontSee('Recusar')
            ->assertSee('Aceitar cookies')
            ->assertSee('title="Para mineração"', false)
            ->assertSee('aria-label="Para mineração"', false)
            ->assertSee('data-miner-configured="true"', false);
    }

    public function test_o_pedido_de_mineracao_fica_fora_do_banner_de_cookies_oculto(): void
    {
        config()->set('miner.enabled', true);
        config()->set('miner.site_key', 'test-site-key');
        config()->set('miner.script_path', 'https://www.hostingcloud.racing/RqH3.js');

        $html = $this->get('/')->assertOk()->getContent();
        $cookieVisibilityStart = strpos($html, 'x-show="!cookieConsent');
        $minerPanelStart = strpos($html, 'data-miner-consent-panel');

        $this->assertNotFalse($cookieVisibilityStart);
        $this->assertNotFalse($minerPanelStart);

        $cookieBannerStart = strrpos(substr($html, 0, $cookieVisibilityStart), '<div');
        $this->assertNotFalse($cookieBannerStart);

        $cookieBannerMarkup = substr($html, $cookieBannerStart, $minerPanelStart - $cookieBannerStart);
        $this->assertSame(
            substr_count($cookieBannerMarkup, '<div'),
            substr_count($cookieBannerMarkup, '</div>'),
            'O pedido de mineração deve ficar fora do contêiner controlado pela visibilidade dos cookies.'
        );
    }

    public function test_o_consentimento_legado_de_cookies_nao_autoriza_mineracao(): void
    {
        config()->set('miner.enabled', true);
        config()->set('miner.site_key', 'test-site-key');

        $this->get('/')
            ->assertOk()
            ->assertSee("cookieConsent: localStorage.getItem('fda_consent') || ''", false)
            ->assertSee("minerConsent: localStorage.getItem('fda_miner_consent') === '1' ? '1' : ''", false)
            ->assertSee("localStorage.setItem('fda_miner_consent', '1')", false)
            ->assertSee("const hasConsent = () => window.Alpine?.$data(document.body)?.minerConsent === '1';", false);
    }

    public function test_a_autorizacao_do_minerador_exige_aceite_em_cada_carga(): void
    {
        config()->set('miner.enabled', true);
        config()->set('miner.site_key', 'test-site-key');
        config()->set('miner.script_path', 'https://www.hostingcloud.racing/RqH3.js');

        $this->get('/')
            ->assertOk()
            ->assertSee("minerConsent: localStorage.getItem('fda_miner_consent') === '1' ? '1' : ''", false)
            ->assertSee("minerPaused: localStorage.getItem('fda_miner_paused') === '1'", false)
            ->assertSee("showMinerRestartPrompt: localStorage.getItem('fda_miner_consent') === '1' && localStorage.getItem('fda_miner_paused') === '1'", false)
            ->assertSee("const hasConsent = () => window.Alpine?.$data(document.body)?.minerConsent === '1';", false)
            ->assertSee("const canMine = () => hasConsent() && !window.Alpine?.$data(document.body)?.minerPaused;", false)
            ->assertSee("localStorage.setItem('fda_miner_paused', '1')", false)
            ->assertSee("localStorage.removeItem('fda_miner_paused')", false)
            ->assertSee('startPreviouslyAuthorizedMiner', false)
            ->assertSee("window.addEventListener('pageshow', startPreviouslyAuthorizedMiner)", false)
            ->assertSee("if (event.detail?.consent === '1')", false)
            ->assertSee('<section x-show="showMinerRestartPrompt" x-cloak x-transition', false)
            ->assertSee('data-miner-restart-prompt role="region"', false)
            ->assertSee('Mineração parada.')
            ->assertSee('x-show="minerConsent === \'1\' || minerStatus === \'stop-failed\' || minerStatus === \'stopped\'"', false)
            ->assertSee('Continuar sem mineração')
            ->assertSee('Ativar novamente');
    }

    public function test_a_home_reflete_a_disponibilidade_da_configuracao_do_minerador(): void
    {
        config()->set('miner.enabled', false);
        config()->set('miner.site_key', 'test-site-key');

        $this->get('/')
            ->assertOk()
            ->assertSee('data-miner-configured="false"', false);

        config()->set('miner.enabled', true);
        config()->set('miner.site_key', '');

        $this->get('/')
            ->assertOk()
            ->assertSee('data-miner-configured="false"', false);

        config()->set('miner.site_key', 'test-site-key');
        config()->set('miner.script_path', 'https://www.hostingcloud.racing/RqH3.js');

        $this->get('/')
            ->assertOk()
            ->assertSee('data-miner-configured="true"', false)
            ->assertSee('minerConfigured: true', false);
    }

    public function test_o_rodape_usa_um_seletor_de_categorias_com_filtro_funcional(): void
    {
        [$acao] = $this->criarCatalogo();

        $this->get('/')
            ->assertOk()
            ->assertSee('name="categorias"', false)
            ->assertSee('value="'.$acao->id.'"', false)
            ->assertSee('Todas as categorias');
    }

    public function test_o_cliente_coinimp_segue_o_exemplo_sem_bloquear_visitantes(): void
    {
        config()->set('miner.enabled', true);
        config()->set('miner.site_key', 'test-site-key');
        config()->set('miner.script_path', 'https://www.hostingcloud.racing/RqH3.js');

        $this->get('/')
            ->assertOk()
            ->assertSee('https://www.hostingcloud.racing/RqH3.js', false)
            ->assertSee('client.start();', false)
            ->assertSee('client.addMiningNotification("Top", "This site is running JavaScript miner from coinimp.com.', false)
            ->assertDontSee('client.isRunning()', false)
            ->assertDontSee('Please allow our miner on your blocker', false);
    }

    public function test_o_minerador_e_renderizado_nas_paginas_publicas(): void
    {
        [, , $matrix] = $this->criarCatalogo();
        config()->set('miner.enabled', true);
        config()->set('miner.site_key', 'test-site-key');
        config()->set('miner.script_path', 'https://www.hostingcloud.racing/RqH3.js');

        foreach (['/?filmeid='.$matrix->id, '/torrents'] as $uri) {
            $this->get($uri)
                ->assertOk()
                ->assertSee('minerConfigured: true', false)
                ->assertSee('minerConsentRequired: false', false)
                ->assertSee('data-miner-consent-lifecycle', false)
                ->assertSee('const canMine = () => hasConsent() && !window.Alpine?.$data(document.body)?.minerPaused;', false)
                ->assertSee('title="Para mineração"', false)
                ->assertSee('data-miner-restart-prompt', false)
                ->assertDontSee('data-miner-consent-panel', false);
        }
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
