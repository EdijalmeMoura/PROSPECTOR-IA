<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Approval;
use App\Models\Lead;
use App\Models\MessageLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MessageLog> */
class MessageLogFactory extends Factory
{
    protected $model = MessageLog::class;

    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'approval_id' => Approval::factory(),
            'campaign_id' => null,
            'channel' => 'email',
            'recipient' => fake()->safeEmail(),
            'status' => 'na_fila',
            'content_snapshot' => fake()->paragraph(),
        ];
    }
}
