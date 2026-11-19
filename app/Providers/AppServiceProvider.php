<?php

namespace App\Providers;

use App\Models\Categoria;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Compartilha a lista de categorias com o menu/rodapé do site público.
        View::composer(['layouts.public', 'catalog.*'], function ($view) {
            try {
                $view->with('categoriasMenu', Categoria::orderBy('name')->get());
            } catch (\Throwable $e) {
                $view->with('categoriasMenu', collect());
            }
        });
    }
}
