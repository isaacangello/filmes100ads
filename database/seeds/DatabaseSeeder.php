<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        for ($i=1;$i <=5000;$i++) {
            $seed_post_id = DB::table('posts')->insertGetId([
                'name' => str_random(15),
                'sinopse' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in ',
                'status1' => 'ativado',
                'status2' => 'aprovado',
                'user_id' => rand(1, 3),
                'ano' => '20' . rand(10, 19),
            ]);
            DB::table('categorias_posts')->insert([
                'post_id' => $seed_post_id,
                'categoria_id' => rand(1, 30),
            ]);
            for ($j = 1; $j <= 6; $j++) {
                DB::table('urls')->insert([
                    'post_id' => $seed_post_id,
                    'label' => 'Openload' . $j,
                    'url' => '<iframe src="https://openload.co/embed/cQUgihywPFU/Discurso_neonazista_do_Bolsonaro_na_Hebraica_Rio.avi.mp4" scrolling="no" frameborder="0" width="700" height="430" allowfullscreen="true" webkitallowfullscreen="true" mozallowfullscreen="true"></iframe>',
                ]);
            }
        }
    }
}
