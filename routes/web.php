<?php

use App\Http\Controllers\AdmConsoleController;
use App\Http\Controllers\AdmController;
use App\Http\Controllers\AdmUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\CadController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\ListCadsController;
use App\Http\Controllers\RendimentosController;
use App\Http\Controllers\TorrentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [IndexController::class, 'index'])->name('index');

Route::get('/torrents', [TorrentController::class, 'index'])->name('torrents.index');
Route::get('/torrents/filme/{post}', [TorrentController::class, 'movie'])->name('torrents.movie');


/*
|--------------------------------------------------------------------------
| Autenticação
|--------------------------------------------------------------------------
|
| Implementação própria no estilo Breeze (o pacote laravel/ui não é
| compatível com o Laravel 11), mantendo as views Blade existentes.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Área administrativa
|--------------------------------------------------------------------------
*/

Route::prefix('/adm')->middleware('auth')->group(function () {
    Route::match(['GET', 'POST'], '/', [AdmController::class, 'index'])->name('adm');

    // Formulário de cadastro de filme.
    Route::get('cadastro', [CadController::class, 'index'])->name('cadastro.index');
    Route::get('cadastro/recortar', [CadController::class, 'crop'])->name('adm.movies.crop');

    // Edição de filme existente.
    Route::get('cadastro/{post}/editar', [CadController::class, 'edit'])->name('adm.movies.edit');

    // Filmes cadastrados pelo usuário.
    Route::get('meuscadastros', [ListCadsController::class, 'index'])->name('meuscadastros.index');
    Route::get('meuscadastros/pag/{pag?}', [ListCadsController::class, 'index'])->name('meuscadastros.pag');

    // Recorte de capa e gravação final.
    Route::match(['GET', 'POST'], '/salvando', [CadController::class, 'postinsert'])->name('cadastro.salvando');

    // Rendimentos.
    Route::get('rendimentos', [RendimentosController::class, 'index'])->name('adm.rendimentos');

    // Gerência de usuários (apenas admins).
    Route::middleware('isAdmin')->group(function () {
        Route::get('usuarios', [AdmUserController::class, 'index'])->name('adm.users.index');
        Route::get('usuarios/criar', [AdmUserController::class, 'create'])->name('adm.users.create');
        Route::post('usuarios', [AdmUserController::class, 'store'])->name('adm.users.store');
        Route::get('usuarios/{user}/editar', [AdmUserController::class, 'edit'])->name('adm.users.edit');
        Route::put('usuarios/{user}', [AdmUserController::class, 'update'])->name('adm.users.update');
        Route::delete('usuarios/{user}', [AdmUserController::class, 'destroy'])->name('adm.users.destroy');

        // Console de comandos (apenas admins).
        Route::get('console', [AdmConsoleController::class, 'index'])->name('adm.console');
        Route::post('console/execute', [AdmConsoleController::class, 'execute'])->name('adm.console.execute');

        // Download do dump SQL gerado pelo comando db:dump.
        Route::get('console/dump-download', [AdmConsoleController::class, 'downloadDump'])
            ->name('adm.console.dump.download');

        // Seed TMDB por lotes via AJAX (evita timeout do InfinityFree).
        Route::post('console/seed-tmdb', [AdmConsoleController::class, 'seedTmdb'])
            ->name('adm.console.seed-tmdb');
        Route::post('console/seed-tmdb/batch', [AdmConsoleController::class, 'seedTmdbBatch'])
            ->name('adm.console.seed-tmdb.batch');
        Route::get('console/seed-tmdb/progress', [AdmConsoleController::class, 'seedTmdbProgress'])
            ->name('adm.console.seed-tmdb.progress');

        // Populador de torrents por lotes via AJAX.
        Route::post('console/populate-torrents', [AdmConsoleController::class, 'populateTorrents'])
            ->name('adm.console.populate-torrents');
        Route::post('console/populate-torrents/batch', [AdmConsoleController::class, 'populateTorrentsBatch'])
            ->name('adm.console.populate-torrents.batch');
        Route::get('console/populate-torrents/progress', [AdmConsoleController::class, 'populateTorrentsProgress'])
            ->name('adm.console.populate-torrents.progress');

        // Dashboard do minerador CoinImp.
        Route::get('minerador', function () {
            return view('adm.minerador');
        })->name('adm.minerador');

        // Diagnóstico do minerador.
        Route::get('miner-diagnostico', function () {
            return view('adm.miner-diagnostico');
        })->name('adm.miner.diagnostico');
    });
});
