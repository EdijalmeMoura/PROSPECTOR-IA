<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeadStage;
use App\Enums\Priority;
use App\Enums\QualificationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    /** @use HasFactory<\Database\Factories\LeadFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'search_job_id',
        'service_id',
        'assigned_user_id',
        'stage',
        'qualification_status',
        'priority',
        'score',
        'score_breakdown',
        'score_justification',
        'recommended_service_reason',
        'opportunity_evidence_summary',
        'last_verified_at',
        'qualified_at',
        'reviewed_at',
        'reviewed_by',
        'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'stage' => LeadStage::class,
            'qualification_status' => QualificationStatus::class,
            'priority' => Priority::class,
            'score_breakdown' => 'array',
            'score' => 'integer',
            'last_verified_at' => 'datetime',
            'qualified_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'is_demo' => 'boolean',
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<SearchJob, $this> */
    public function searchJob(): BelongsTo
    {
        return $this->belongsTo(SearchJob::class);
    }

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** @return BelongsTo<User, $this> */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /** @return HasMany<LeadContact, $this> */
    public function contacts(): HasMany
    {
        return $this->hasMany(LeadContact::class);
    }

    /** @return HasMany<LeadEvidence, $this> */
    public function evidence(): HasMany
    {
        return $this->hasMany(LeadEvidence::class);
    }

    /** @return HasMany<LeadNote, $this> */
    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class);
    }

    /** @return HasMany<FollowUpTask, $this> */
    public function tasks(): HasMany
    {
        return $this->hasMany(FollowUpTask::class);
    }

    /** @return HasMany<LeadStageHistory, $this> */
    public function stageHistory(): HasMany
    {
        return $this->hasMany(LeadStageHistory::class);
    }

    /** @return HasMany<Approval, $this> */
    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class);
    }

    /** @return HasMany<MessageLog, $this> */
    public function messageLogs(): HasMany
    {
        return $this->hasMany(MessageLog::class);
    }

    /** @return HasMany<Proposal, $this> */
    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    /** @return HasOne<Deal, $this> */
    public function deal(): HasOne
    {
        return $this->hasOne(Deal::class);
    }

    /** @return Builder<Lead> */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_demo', false);
    }
}
