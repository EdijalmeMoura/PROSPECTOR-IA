<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('prospector.service_catalog') as $service) {
            Service::updateOrCreate(
                ['code' => $service['code']],
                [
                    'name' => $service['name'],
                    'description' => $service['description'],
                    'price' => $service['price'],
                    'is_active' => $service['is_active'],
                ],
            );
        }
    }
}
