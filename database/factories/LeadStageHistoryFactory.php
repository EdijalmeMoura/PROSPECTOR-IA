<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadStageHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LeadStageHistory> */
class LeadStageHistoryFactory extends Factory
{
    protected $model = LeadStageHistory::class;

    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'user_id' => null,
            'from_stage' => 'novo',
            'to_stage' => 'pesquisado',
            'reason' => null,
        ];
    }
}
