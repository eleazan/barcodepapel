<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a default admin user for development
        User::factory()->create([
            'name'     => 'Admin',
            'email'    => 'admin@barcodepapel.test',
            'is_admin' => true,
        ]);

        // Create additional test users
        if (app()->environment('local', 'testing')) {
            User::factory(9)->create();
        }

        // Seed catalog: categories, products, delivery zones, orders
        $this->call(CatalogSeeder::class);
    }
}
