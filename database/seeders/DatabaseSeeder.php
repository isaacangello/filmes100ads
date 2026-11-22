<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Cria os usuários administradores
        $this->call(UsersTableSeeder::class);

        // Busca filmes reais da TMDB API (requer TMDB_API_KEY no .env)
        // Se a chave não estiver configurada, o seeder mostra uma mensagem e pula
        $this->call(TmdbMoviesSeeder::class);
    }
}

