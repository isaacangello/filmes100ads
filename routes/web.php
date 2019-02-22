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
Route::get('/adm', function () {
    return view('adm.adm');
});

// toras posts
Route::post('/', function () {
    return view('posts.index');
});
Route::any('adm/viewchar/{viewchar?}', function ($viewchar = 'adm') {
    return view('adm.adm');
})->name('adm');


Route::view('adm/rendimentos','adm.rendimentos');
Route::view('adm/cadastro','adm.cad');

Route::post('/login', function () {
    return view('auth.login');
});

Auth::routes();
Route::get('/', 'IndexsController@index');
Route::resource('/','IndexsController');

Route::resource('/adm','AdmController');

Route::resource('adm/rendimentos','RendimentosController');
Route::resource('adm/cadastro','CadController');
//Route::get('/', 'HomeController@index')->name('home');
