<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Cria os usuários administradores pré-cadastrados.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => 'Isaac',
                'email' => 'isaacangello@gmail.com',
                'password' => bcrypt('password'),
            ],
            [
                'name' => 'Root',
                'email' => 'isaacangello@inf.ufpel.edu.br',
                'password' => bcrypt('password'),
            ],
        ];

        foreach ($users as $userData) {
            // Evita duplicação se o seeder for executado mais de uma vez
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
