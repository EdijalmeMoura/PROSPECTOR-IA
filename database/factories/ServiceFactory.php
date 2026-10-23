<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'code' => 'SERV_' . fake()->unique()->numerify('####'),
            'name' => fake()->randomElement(['Pacote de artes', 'Logotipo', 'Melhoria WordPress']),
            'description' => fake()->sentence(),
            'price' => fake()->randomElement(['200.00', '250.00', '300.00']),
            'is_active' => true,
        ];
    }
}
