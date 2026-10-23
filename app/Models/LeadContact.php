<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContactPermission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadContact extends Model
{
    /** @use HasFactory<\Database\Factories\LeadContactFactory> */
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'channel',
        'value',
        'permission_status',
        'permission_verified_at',
        'verified_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'permission_status' => ContactPermission::class,
            'permission_verified_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
