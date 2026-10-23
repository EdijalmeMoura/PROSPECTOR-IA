<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ChannelType;
use App\Enums\MessageStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageLog extends Model
{
    /** @use HasFactory<\Database\Factories\MessageLogFactory> */
    use HasFactory;

    protected $fillable = [
        'lead_id', 'approval_id', 'campaign_id', 'channel', 'recipient', 'status',
        'provider_message_id', 'content_snapshot', 'provider_payload', 'error_message',
        'sent_at', 'delivered_at', 'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'channel' => ChannelType::class,
            'status' => MessageStatus::class,
            'provider_payload' => 'array',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** @return BelongsTo<Approval, $this> */
    public function approval(): BelongsTo
    {
        return $this->belongsTo(Approval::class);
    }
}
