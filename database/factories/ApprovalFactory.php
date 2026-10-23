<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ApprovalStatus;
use App\Enums\ChannelType;
use App\Models\Approval;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Approval> */
class ApprovalFactory extends Factory
{
    protected $model = Approval::class;

    public function definition(): array
    {
        $channel = ChannelType::Email;
        $recipient = fake()->safeEmail();
        $text = 'Olá, gostaríamos de apresentar nossos serviços.';

        return [
            'lead_id' => Lead::factory(),
            'ai_generation_id' => null,
            'approver_id' => null,
            'channel' => $channel,
            'recipient' => $recipient,
            'content_version' => 1,
            'full_text' => $text,
            'evidence_snapshot' => [],
            'channel_permission' => 'desconhecida',
            'status' => ApprovalStatus::Pendente,
            'content_hash' => Approval::hashContent($channel, $recipient, $text),
        ];
    }
}
