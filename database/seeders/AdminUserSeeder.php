<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Cria o primeiro administrador do sistema se não existir.
 *
 * O email do admin é configurável via ADMIN_EMAIL no .env.
 */
class AdminUserSeeder extends Seeder
{
    /**
     * Executa o seeder.
     */
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@filmes100ads.local');
        $adminName = env('ADMIN_NAME', 'Administrador');
        $adminPassword = env('ADMIN_PASSWORD', 'admin123');

        User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'email' => $adminEmail,
                'password' => bcrypt($adminPassword),
                'is_admin' => true,
            ]
        );

        // Se já existe um usuário com esse email mas não é admin, promove-o
        $user = User::where('email', $adminEmail)->first();
        if ($user && ! $user->is_admin) {
            $user->update(['is_admin' => true]);
        }
    }
}
