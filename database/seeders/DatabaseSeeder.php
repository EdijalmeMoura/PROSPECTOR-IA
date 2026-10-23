<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ServiceCatalogSeeder::class,
            AdminUserSeeder::class,
        ]);

        // Demonstração (dados fictícios) só quando executada explicitamente:
        // php artisan db:seed --class=DemoSeeder
        if (env('SEED_DEMO_DATA') === 'true') {
            $this->call(DemoSeeder::class);
        }
    }
}
