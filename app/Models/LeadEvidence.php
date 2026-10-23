<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EvidenceReliability;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadEvidence extends Model
{
    /** @use HasFactory<\Database\Factories\LeadEvidenceFactory> */
    use HasFactory;

    protected $table = 'lead_evidence';

    protected $fillable = [
        'lead_id',
        'ai_generation_id',
        'type',
        'description',
        'source_url',
        'reliability',
        'collected_by',
        'observed_at',
    ];

    protected function casts(): array
    {
        return [
            'reliability' => EvidenceReliability::class,
            'observed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
