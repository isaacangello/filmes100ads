<?php

use App\Http\Controllers\AdmController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
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

    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

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
    Route::post('cadastro', [CadController::class, 'store'])->name('cadastro.store');

    // Filmes cadastrados pelo usuário.
    Route::get('meuscadastros', [ListCadsController::class, 'index'])->name('meuscadastros.index');
    Route::get('meuscadastros/pag/{pag?}', [ListCadsController::class, 'index'])->name('meuscadastros.pag');

    // Recorte de capa e gravação final.
    Route::match(['GET', 'POST'], '/salvando', [CadController::class, 'postinsert'])->name('cadastro.salvando');

    // Rendimentos.
    Route::get('rendimentos', [RendimentosController::class, 'index'])->name('adm.rendimentos');
});
