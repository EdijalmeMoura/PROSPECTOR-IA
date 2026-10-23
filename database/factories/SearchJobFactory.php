<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SearchJob;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SearchJob>
 */
class SearchJobFactory extends Factory
{
    protected $model = SearchJob::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'service_id' => Service::factory(),
            'provider' => 'null',
            'status' => 'queued',
            'segment' => 'Todos',
            'cities' => [config('prospector.region.default_city')],
            'state' => 'PE',
            'target_quantity' => 50,
            'found_count' => 0,
            'duplicate_count' => 0,
            'rejected_count' => 0,
            'qualified_count' => 0,
            'progress' => 0,
        ];
    }
}
