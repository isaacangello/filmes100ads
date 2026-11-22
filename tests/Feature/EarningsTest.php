<?php

namespace Tests\Feature;

use App\Livewire\EarningsDashboard;
use App\Models\Withdrawal;
use App\Services\EarningsService;
use Livewire\Livewire;

class EarningsTest extends LivewireTestCase
{
    public function test_viewing_a_movie_records_a_view(): void
    {
        [, , $matrix] = $this->criarCatalogo();

        $service = app(EarningsService::class);
        $result = $service->trackView($matrix->id, null, '127.0.0.1');

        $this->assertTrue($result);
        $this->assertDatabaseCount('movie_views', 1);
    }

    public function test_duplicate_view_from_same_ip_is_ignored(): void
    {
        [, , $matrix] = $this->criarCatalogo();

        $service = app(EarningsService::class);
        $service->trackView($matrix->id, null, '127.0.0.1');
        $result = $service->trackView($matrix->id, null, '127.0.0.1');

        $this->assertFalse($result);
        $this->assertDatabaseCount('movie_views', 1);
    }

    public function test_earnings_calculates_gains_correctly(): void
    {
        [, , $matrix] = $this->criarCatalogo();
        // O Matrix pertence ao user criado por criarCatalogo() com email teste@fda.local
        $user = \App\Models\User::where('email', 'teste@fda.local')->first();

        $service = app(EarningsService::class);
        $service->trackView($matrix->id, null, '10.0.0.1');
        $service->trackView($matrix->id, null, '10.0.0.2');
        $service->trackView($matrix->id, null, '10.0.0.3');

        $stats = $service->getStats($user->id);

        $this->assertEquals(3, $stats['views_total']);
        $this->assertEquals(0.03, $stats['earnings_total']);
    }

    public function test_withdrawal_history_is_empty_by_default(): void
    {
        $user = $this->criarUsuario();
        $this->actingAs($user);

        Livewire::test(EarningsDashboard::class)
            ->assertOk()
            ->assertSee('Nenhum saque registrado');
    }

    public function test_movie_detail_tracks_view_on_mount(): void
    {
        [, , $matrix] = $this->criarCatalogo();

        $this->get('/?filmeid='.$matrix->id)
            ->assertOk();

        $this->assertDatabaseCount('movie_views', 1);
    }
}
