<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Deal;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Deal> */
class DealFactory extends Factory
{
    protected $model = Deal::class;

    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'proposal_id' => null,
            'status' => 'aberto',
            'expected_amount' => '300.00',
            'received_amount' => '0.00',
        ];
    }
}
