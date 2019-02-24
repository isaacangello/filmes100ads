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
Route::match(['GET', 'POST'], '/', function(Request $request)
{
    return view('adm.adm' ,['request' => $request]);

});
Route::any('adm/{view?}', function($view = 'adm')
{
    if($view == 'adm'){
    return view('adm.adm' ,['view' => $view]);
    }
    if($view == 'cadastro'){
        return view('adm.cadastro');
    }
    if($view == 'rendimentos'){
        return view('adm.rendimentos');
    }

})->name('param');

Route::prefix('/adm/')->name('adm')->group(function () {

    Route::get('/cadastro', function (){return view('adm.cadastro');})->name('cadastro');
    Route::get('/redimentos', function (){return view('adm.rendimentos')->name('redimentos');});

    Route::resource('/rendimentos','RendimentosController');
    Route::resource('/cadastro','CadController');


});

Route::resource('/adm','AdmController');

//Route::any('/adm', function (){return view('adm.adm');})->name('adm');



Route::post('/login', function () {
    return view('auth.login');
});

Auth::routes();
Route::get('/', 'IndexsController@index');
Route::resource('/','IndexsController');

//Route::get('/', 'HomeController@index')->name('home');
