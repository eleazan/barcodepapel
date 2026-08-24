<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        // Los datos de demo necesitan faker, que no se instala con --no-dev:
        // sin él se siembra el catálogo real aunque el entorno diga "local"
        // (p. ej. si la config quedó cacheada con el APP_ENV del build).
        $demo = app()->environment('local', 'testing') && class_exists(Factory::class);

        if ($demo) {
            User::factory(9)->create();
            $this->call(CatalogSeeder::class);

            return;
        }

        $this->call(ProductionSeeder::class);
    }
}
