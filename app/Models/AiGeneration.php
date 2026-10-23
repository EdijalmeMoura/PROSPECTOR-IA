<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AiKind;
use App\Enums\AiStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGeneration extends Model
{
    /** @use HasFactory<\Database\Factories\AiGenerationFactory> */
    use HasFactory;

    protected $fillable = [
        'lead_id', 'user_id', 'kind', 'provider', 'model', 'status',
        'evidence_ids_used', 'input_payload', 'structured_output', 'uncertainties',
        'prompt_tokens', 'completion_tokens', 'cost_brl', 'error_message',
    ];

    protected function casts(): array
    {
        return [
            'kind' => AiKind::class,
            'status' => AiStatus::class,
            'evidence_ids_used' => 'array',
            'input_payload' => 'array',
            'structured_output' => 'array',
            'uncertainties' => 'array',
            'cost_brl' => 'decimal:4',
        ];
    }

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
