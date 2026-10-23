<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SearchJobStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SearchJob extends Model
{
    /** @use HasFactory<\Database\Factories\SearchJobFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'provider',
        'status',
        'segment',
        'cities',
        'state',
        'target_quantity',
        'found_count',
        'duplicate_count',
        'rejected_count',
        'qualified_count',
        'progress',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SearchJobStatus::class,
            'cities' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return HasMany<Lead, $this> */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /** @return Builder<SearchJob> */
    public function scopeFinished(Builder $query): Builder
    {
        return $query->whereIn('status', [
            SearchJobStatus::Completed->value,
            SearchJobStatus::Failed->value,
            SearchJobStatus::Cancelled->value,
        ]);
    }
}
