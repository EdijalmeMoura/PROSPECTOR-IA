<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Campaign> */
class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'user_id' => User::factory(),
            'name' => fake()->sentence(3),
            'channel' => 'email',
            'status' => 'rascunho',
            'target_count' => 10,
        ];
    }
}
