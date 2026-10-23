<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@prospector.local');
        $password = env('ADMIN_PASSWORD');

        if (! $password) {
            // Em produção defina ADMIN_PASSWORD no .env. Senha temporária apenas local.
            $password = 'trocar-minha-senha';
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrador',
                'password' => Hash::make($password),
                'role' => UserRole::Admin->value,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}
