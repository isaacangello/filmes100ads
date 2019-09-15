<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
 * DEscoberto Problema nas rotas
 * resoluçáo
 * por falta de conhecimento adicionei em lugar errado um metodo midleware
 * gerando um comportamento idesejado nas rotas
 * lição aprendida, se não sabe exatamente o que é
 * não mexa ou simplesmento procure saber sobre.
 * */
Route::prefix('/adm')->group(function () {
    Route::match(['GET', 'POST'], '/', "AdmController@index")->name('adm');

    Route::resource('cadastro','CadController');
    Route::get('meuscadastros/pag/{pag?}', "ListcadsController@index")->name('meuscadastros.index');
    Route::resource('meuscadastros','ListcadsController');
    Route::match(['GET', 'POST'], '/salvando', "CadController@postinsert")->name('cadastro.salvando');
});



Route::post('/login', function () {
    return view('auth.login');
});

Auth::routes();


Route::get('/', 'IndexController@index')->name('index');
