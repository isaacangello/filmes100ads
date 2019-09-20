<?php

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
        // $this->call(UsersTableSeeder::class);
        DB::table('categorias')->insert([
            'name' => 'Ação',
            'detalhes' => 'Filmes de Ação geralmente são filmes que envolvem atividades geralmente físicas intensas ou não durante a Filmagens.',


        ]);
        DB::table('categorias')->insert([
            'name' => 'Animação',
            'detalhes' => 'Sem detalhes.',


        ]);
        DB::table('categorias')->insert([
            'name' => 'Comédia',
            'detalhes' => 'Sem detalhes.',

        ]);

        DB::table('categorias')->insert([
            'name' => 'Comédia romântica',
            'detalhes' => 'Sem detalhes.',

        ]);
        DB::table('categorias')->insert([
            'name' => 'Comédia dramática',
            'detalhes' => 'Sem detalhes.',

        ]);
        DB::table('categorias')->insert([
            'name' => 'Comédia de ação',
            'detalhes' => 'Sem detalhes.',

        ]);
        DB::table('categorias')->insert([
            'name' => 'Dança',
            'detalhes' => 'Sem detalhes.',

        ]);
        DB::table('categorias')->insert([
            'name' => 'Documentário',
            'detalhes' => 'Sem detalhes.',

        ]);
        DB::table('categorias')->insert([
            'name' => 'Docuficção',
            'detalhes' => 'Sem detalhes.',

        ]);

        DB::table('categorias')->insert([
            'name' => 'Drama',
            'detalhes' => 'Sem detalhes.',

        ]);
        DB::table('categorias')->insert([
            'name' => 'Espionagem',
            'detalhes' => 'Sem detalhes.',

        ]);
        DB::table('categorias')->insert([
            'name' => 'Faroeste (ou western)',
            'detalhes' => 'Sem detalhes.',

        ]);
        DB::table('categorias')->insert([
            'name' => 'Ficção científica',
            'detalhes' => 'Sem detalhes.',

        ]);
        DB::table('categorias')->insert([
            'name' => 'Filmes de guerra',
            'detalhes' => 'Sem detalhes.',

        ]);
        DB::table('categorias')->insert([
            'name' => 'Musical',
            'detalhes' => 'Sem detalhes.',

        ]);
        DB::table('categorias')->insert([
            'name' => 'Filme policial',
            'detalhes' => 'Sem detalhes.',

        ]);
        DB::table('categorias')->insert([
            'name' => 'Romance',
            'detalhes' => 'Sem detalhes.',

        ]);
        DB::table('categorias')->insert([
            'name' => 'Seriado',
            'detalhes' => 'Sem detalhes.',

        ]);
        DB::table('categorias')->insert([
            'name' => 'Suspense',
            'detalhes' => 'Sem detalhes.',

        ]);
        DB::table('categorias')->insert([
            'name' => 'Terror',
            'detalhes' => 'Sem detalhes.',

        ]);
        DB::table('categorias')->insert([
            'name' => 'Outros',
            'detalhes' => 'Categoria Padrão, que vão os filmes sem categoria ',

        ]);

        for ($i=1;$i <=5000;$i++) {
            $seed_post_id = DB::table('posts')->insertGetId([
                'name' => Str::random(15),
                'realname' => Str::random(10),
                'sinopse' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in ',
                'status1' => 'ativado',
                'status2' => 'aprovado',
                'user_id' => rand(1, 3),
                'diretor' => Str::random(10),
                'duracao' => rand(100, 180),
                'ano' => '20' . rand(10, 19),
                'pais' => 'Estrangeiro',
            ]);
            DB::table('categorias_posts')->insert([
                'post_id' => $seed_post_id,
                'categoria_id' => rand(1, 21),
            ]);
            for ($j = 1; $j <= 6; $j++) {
                DB::table('urls')->insert([
                    'post_id' => $seed_post_id,
                    'label' => 'Thevid' . $j,
                    'url' => '<video class="fp-engine hlsjs-engine" autoplay="" x-webkit-airplay="allow" src="blob:https://akugyash.com/5ba4d8d6-66d2-4be0-b2f4-9624c7ca80d7"></video>',
                ]);
            }
        }
    }
}

