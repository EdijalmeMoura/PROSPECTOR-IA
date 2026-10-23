<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadEvidence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadEvidence>
 */
class LeadEvidenceFactory extends Factory
{
    protected $model = LeadEvidence::class;

    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'type' => fake()->randomElement(['site_ausente', 'site_desatualizado', 'redes_sem_padrao']),
            'description' => fake()->sentence(),
            'source_url' => fake()->url(),
            'reliability' => 'media',
            'collected_by' => 'manual',
            'observed_at' => now(),
        ];
    }
}
