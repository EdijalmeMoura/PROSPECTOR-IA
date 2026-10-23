<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptOut extends Model
{
    /** @use HasFactory<\Database\Factories\OptOutFactory> */
    use HasFactory;

    protected $table = 'opt_outs';

    protected $fillable = ['lead_id', 'channel', 'identifier', 'source', 'reason', 'requested_at'];

    protected function casts(): array
    {
        return ['requested_at' => 'datetime'];
    }

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
