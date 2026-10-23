<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Support\DomainNormalizer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $name = fake()->company();
        $website = 'https://' . fake()->unique()->slug(2) . '.com.br';

        return [
            'name' => $name,
            'trade_name' => $name,
            'segment' => fake()->randomElement(config('prospector.segments')),
            'city' => fake()->randomElement(config('prospector.region.cities')),
            'state' => 'PE',
            'website' => $website,
            'normalized_domain' => DomainNormalizer::normalize($website),
            'place_id' => null,
            'legal_basis' => 'legitimo_interesse',
            'processing_purpose' => 'Prospecção comercial de serviços de design e web',
            'is_demo' => false,
            'last_verified_at' => now(),
        ];
    }

    public function demo(): static
    {
        return $this->state(fn () => ['is_demo' => true]);
    }

    public function withoutWebsite(): static
    {
        return $this->state(fn () => ['website' => null, 'normalized_domain' => null]);
    }
}
