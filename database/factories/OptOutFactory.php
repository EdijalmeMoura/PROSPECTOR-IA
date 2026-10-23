<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OptOut;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OptOut> */
class OptOutFactory extends Factory
{
    protected $model = OptOut::class;

    public function definition(): array
    {
        return [
            'lead_id' => null,
            'channel' => 'email',
            'identifier' => fake()->unique()->safeEmail(),
            'source' => 'manual',
            'reason' => null,
            'requested_at' => now(),
        ];
    }
}
