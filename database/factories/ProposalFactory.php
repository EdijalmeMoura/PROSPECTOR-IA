<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lead;
use App\Models\Proposal;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Proposal> */
class ProposalFactory extends Factory
{
    protected $model = Proposal::class;

    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'service_id' => Service::factory(),
            'ai_generation_id' => null,
            'status' => 'rascunho',
            'amount' => '250.00',
            'content' => fake()->paragraph(),
            'valid_until' => now()->addDays(7)->toDateString(),
        ];
    }
}
