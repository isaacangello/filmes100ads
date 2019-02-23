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
// rotas get
Route::get('/', function () {
    return view('posts.index');
});

// toras posts
Route::post('/', function () {
    return view('posts.index');
});
/*Route::any('adm/viewchar/{viewchar?}', function ($viewchar = 'adm') {
    return view('adm.adm', ['viewchar' => $viewchar]);
})->name('adm');
*/

//Route::view('adm/rendimentos','adm.rendimentos');
Route::prefix('adm')->group(function () {
    Route::any('/', function (){return view('adm.adm');});
    Route::any('/cadastro', function (){return view('adm.cadastro');});
    Route::any('/redimentos', function (){return view('adm.Rendimentos');});
    Route::resource('/','AdmController');
    Route::resource('/rendimentos','RendimentosController');
    Route::resource('/cadastro','CadController');


});
    

//Route::any('/adm', function (){return view('adm.adm');})->name('adm');



Route::post('/login', function () {
    return view('auth.login');
});

Auth::routes();
Route::get('/', 'IndexsController@index');
Route::resource('/','IndexsController');

//Route::get('/', 'HomeController@index')->name('home');
