<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AiGeneration;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AiGeneration> */
class AiGenerationFactory extends Factory
{
    protected $model = AiGeneration::class;

    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'user_id' => User::factory(),
            'kind' => 'resumo',
            'provider' => 'null',
            'model' => null,
            'status' => 'indisponivel',
            'evidence_ids_used' => [],
            'input_payload' => [],
            'structured_output' => null,
            'error_message' => 'Provedor de IA não configurado.',
        ];
    }
}
