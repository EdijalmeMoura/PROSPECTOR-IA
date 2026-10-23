<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RetentionCondition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySource extends Model
{
    /** @use HasFactory<\Database\Factories\CompanySourceFactory> */
    use HasFactory;

    protected $table = 'company_sources';

    protected $fillable = [
        'company_id',
        'source_name',
        'source_url',
        'external_id',
        'retention_condition',
        'metadata',
        'collected_at',
        'verified_at',
        'retention_expires_at',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'retention_condition' => RetentionCondition::class,
            'collected_at' => 'datetime',
            'verified_at' => 'datetime',
            'retention_expires_at' => 'datetime',
            'is_primary' => 'boolean',
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
