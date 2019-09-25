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
    Route::match(['GET', 'POST'],'meuscadastros/pag/{pag?}', "ListcadsController@index")->name('meuscadastros.index');
    Route::match(['PUT', 'POST','PATCH'],'meuscadastros/pag/{pag?}', "ListcadsController@modstatus")->name('meuscadastros.modstatus');
    Route::resource('rendimentos','RendimentosController');
    Route::any('/salvando', "CadController@postinsert")->name('cadastro.salvando');
});



Route::post('/login', function () {
    return view('auth.login');
});
Route::match(['GET','POST'],'/home',function(){
    return redirect()->route('adm');
});
Auth::routes();


Route::get('/', 'IndexController@index')->name('index');
