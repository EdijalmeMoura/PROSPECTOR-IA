<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadContact>
 */
class LeadContactFactory extends Factory
{
    protected $model = LeadContact::class;

    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'channel' => 'email',
            'value' => fake()->safeEmail(),
            'permission_status' => 'desconhecida',
            'verified_at' => now(),
        ];
    }
}
