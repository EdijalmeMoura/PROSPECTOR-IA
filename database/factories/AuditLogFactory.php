<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AuditLog> */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => 'test.action',
            'auditable_type' => null,
            'auditable_id' => null,
            'changes' => [],
            'ip_address' => fake()->ipv4(),
            'created_at' => now(),
        ];
    }
}
