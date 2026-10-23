<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DealStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deal extends Model
{
    /** @use HasFactory<\Database\Factories\DealFactory> */
    use HasFactory;

    protected $fillable = [
        'lead_id', 'proposal_id', 'status', 'expected_amount',
        'received_amount', 'won_at', 'lost_at', 'lost_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => DealStatus::class,
            'expected_amount' => 'decimal:2',
            'received_amount' => 'decimal:2',
            'won_at' => 'datetime',
            'lost_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** @return BelongsTo<Proposal, $this> */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }
}
