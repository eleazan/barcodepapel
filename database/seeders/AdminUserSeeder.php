<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email    = env('ADMIN_EMAIL', 'admin@barcodepapel.es');
        $password = env('ADMIN_PASSWORD');

        if (! $password) {
            $this->command->error('Define ADMIN_PASSWORD en .env antes de ejecutar este seeder.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name'              => 'Barco de Papel',
                'email_verified_at' => now(),
                'password'          => Hash::make($password),
                'is_admin'          => true,
            ],
        );

        $this->command->info("Admin creado: {$email}");
    }
}
