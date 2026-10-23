<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Enums\ChannelType;
use App\Enums\ContactPermission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Approval extends Model
{
    /** @use HasFactory<\Database\Factories\ApprovalFactory> */
    use HasFactory;

    protected $fillable = [
        'lead_id', 'ai_generation_id', 'approver_id', 'channel', 'recipient',
        'content_version', 'full_text', 'evidence_snapshot', 'channel_permission',
        'status', 'approved_at', 'rejected_at', 'decision_note', 'content_hash',
    ];

    protected function casts(): array
    {
        return [
            'channel' => ChannelType::class,
            'status' => ApprovalStatus::class,
            'channel_permission' => ContactPermission::class,
            'evidence_snapshot' => 'array',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /** @return BelongsTo<AiGeneration, $this> */
    public function aiGeneration(): BelongsTo
    {
        return $this->belongsTo(AiGeneration::class);
    }

    /** @return HasMany<MessageLog, $this> */
    public function messageLogs(): HasMany
    {
        return $this->hasMany(MessageLog::class);
    }

    public static function hashContent(ChannelType $channel, string $recipient, string $text): string
    {
        return hash('sha256', $channel->value . '|' . mb_strtolower(trim($recipient)) . '|' . $text);
    }
}
