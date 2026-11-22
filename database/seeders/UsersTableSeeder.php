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
                'is_admin' => true,
            ],
            [
                'name' => 'Root',
                'email' => 'isaacangello@inf.ufpel.edu.br',
                'password' => bcrypt('password'),
                'is_admin' => true,
            ],
        ];

        foreach ($users as $userData) {
            // Cria ou atualiza: se o usuário já existe, garante que é admin
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            if (! $user->is_admin) {
                $user->update(['is_admin' => true]);
            }
        }
    }
}
