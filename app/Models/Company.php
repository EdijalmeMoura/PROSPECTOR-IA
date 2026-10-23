<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'trade_name',
        'segment',
        'city',
        'state',
        'website',
        'normalized_domain',
        'place_id',
        'legal_basis',
        'processing_purpose',
        'is_demo',
        'last_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_demo' => 'boolean',
            'last_verified_at' => 'datetime',
        ];
    }

    /** @return HasMany<CompanySource, $this> */
    public function sources(): HasMany
    {
        return $this->hasMany(CompanySource::class);
    }

    /** @return HasMany<Lead, $this> */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /** @return Builder<Company> */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_demo', false);
    }

    /** @return Builder<Company> */
    public function scopeDemo(Builder $query): Builder
    {
        return $query->where('is_demo', true);
    }
}
