<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FollowUpTask;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FollowUpTask> */
class FollowUpTaskFactory extends Factory
{
    protected $model = FollowUpTask::class;

    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(),
            'due_at' => now()->addDays(2),
            'completed_at' => null,
        ];
    }
}
