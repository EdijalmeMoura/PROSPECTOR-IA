<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Models\CompanySource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanySource>
 */
class CompanySourceFactory extends Factory
{
    protected $model = CompanySource::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'source_name' => 'manual',
            'source_url' => fake()->url(),
            'external_id' => 'manual:' . fake()->unique()->uuid(),
            'retention_condition' => 'permanente',
            'collected_at' => now(),
            'verified_at' => now(),
            'is_primary' => true,
        ];
    }
}
