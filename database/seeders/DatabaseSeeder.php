<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        if (app()->environment('local', 'testing')) {
            User::factory(9)->create();
            $this->call(CatalogSeeder::class);

            return;
        }

        $this->call(ProductionSeeder::class);
    }
}
