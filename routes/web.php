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

Route::get('/', function () {
    return view('posts.index');
});
Route::post('/', function () {
    return view('posts.index');
});
Route::post('/adm', function () {
    return view('adm.adm');
});
Route::view('/adm/rendimentos','adm.rendimentos');
/*
Route::post('/adm/rendimentos', function () {
    return view('adm.rendimentos');
});
*/
Route::post('/login', function () {
    return view('auth.login');
});

Auth::routes();
Route::get('/', 'IndexsController@index');
Route::resource('/','IndexsController');
Route::resource('/adm','AdmController');
//Route::resource('/adm/rendimentos','AdmController');
Route::resource('/adm/rendimentos','RendimentosController');

//Route::get('/', 'HomeController@index')->name('home');
