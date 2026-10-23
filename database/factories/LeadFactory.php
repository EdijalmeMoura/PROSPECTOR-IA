<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Models\Lead;
use App\Models\SearchJob;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'search_job_id' => null,
            'service_id' => Service::factory(),
            'assigned_user_id' => User::factory(),
            'stage' => 'novo',
            'qualification_status' => 'pendente',
            'priority' => 'sem_evidencias',
            'score' => 0,
            'score_breakdown' => null,
            'score_justification' => null,
            'last_verified_at' => now(),
            'is_demo' => false,
        ];
    }

    public function demo(): static
    {
        return $this->state(fn () => ['is_demo' => true]);
    }
}
