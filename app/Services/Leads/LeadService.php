<?php

declare(strict_types=1);

namespace App\Services\Leads;

use App\Enums\LeadStage;
use App\Enums\RetentionCondition;
use App\Models\Company;
use App\Models\CompanySource;
use App\Models\Lead;
use App\Models\User;
use App\Services\Dedup\CompanyDeduplicator;
use App\Services\Scoring\LeadScorer;
use App\Support\AuditLogger;
use App\Support\DomainNormalizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeadService
{
    public function __construct(
        private readonly CompanyDeduplicator $deduplicator,
        private readonly LeadScorer $scorer,
    ) {}

    /**
     * Cria empresa (ou reaproveita existente por identificador confiável),
     * lead, contatos, evidências e fonte de origem em uma transação.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $contacts
     * @param  array<int, array<string, mixed>>  $evidence
     */
    public function create(array $data, array $contacts = [], array $evidence = [], User $user, string $sourceName = 'manual'): Lead
    {
        return DB::transaction(function () use ($data, $contacts, $evidence, $user, $sourceName) {
            $company = $this->deduplicator->findExisting($data['place_id'] ?? null, $data['website'] ?? null);

            if ($company === null) {
                $company = Company::create([
                    'name' => $data['company_name'],
                    'trade_name' => $data['trade_name'] ?? null,
                    'segment' => $data['segment'] ?? null,
                    'city' => $data['city'],
                    'state' => $data['state'] ?? config('prospector.region.state'),
                    'website' => $data['website'] ?? null,
                    'normalized_domain' => DomainNormalizer::normalize($data['website'] ?? null),
                    'place_id' => $data['place_id'] ?? null,
                    'is_demo' => (bool) ($data['is_demo'] ?? false),
                    'last_verified_at' => now(),
                ]);
            }

            CompanySource::firstOrCreate(
                [
                    'source_name' => $sourceName,
                    'external_id' => $data['place_id'] ?? ('manual:' . md5($company->id . '|' . ($data['company_name'] ?? ''))),
                ],
                [
                    'company_id' => $company->id,
                    'source_url' => $data['website'] ?? null,
                    'retention_condition' => isset($data['place_id'])
                        ? RetentionCondition::Transitoria->value
                        : RetentionCondition::Permanente->value,
                    'collected_at' => now(),
                    'verified_at' => now(),
                    'retention_expires_at' => isset($data['place_id'])
                        ? now()->addDays((int) config('prospector.retention.transient_place_data_days'))
                        : null,
                    'is_primary' => true,
                ],
            );

            $lead = Lead::create([
                'company_id' => $company->id,
                'search_job_id' => $data['search_job_id'] ?? null,
                'service_id' => $data['service_id'] ?? null,
                'assigned_user_id' => $user->id,
                'stage' => LeadStage::Novo,
                'is_demo' => (bool) ($data['is_demo'] ?? false),
                'last_verified_at' => now(),
            ]);

            foreach ($contacts as $contact) {
                $lead->contacts()->create([
                    'channel' => $contact['channel'],
                    'value' => $contact['value'],
                    'permission_status' => $contact['permission_status'] ?? 'desconhecida',
                    'verified_at' => now(),
                ]);
            }

            foreach ($evidence as $item) {
                $lead->evidence()->create([
                    'type' => $item['type'],
                    'description' => $item['description'],
                    'source_url' => $item['source_url'] ?? null,
                    'reliability' => $item['reliability'] ?? 'media',
                    'collected_by' => $item['collected_by'] ?? 'manual',
                    'observed_at' => $item['observed_at'] ?? now(),
                ]);
            }

            $this->applyScore($lead);

            AuditLogger::record('lead.created', $lead, ['company' => $company->name, 'source' => $sourceName]);

            return $lead->fresh(['company', 'service', 'contacts', 'evidence']);
        });
    }

    /**
     * Recalcula e persiste a pontuação explicável do lead.
     */
    public function applyScore(Lead $lead): Lead
    {
        $lead->loadMissing(['evidence', 'contacts', 'company', 'service']);
        $result = $this->scorer->score($lead);

        $lead->update([
            'score' => $result->total,
            'score_breakdown' => $result->criteria,
            'score_justification' => collect($result->criteria)
                ->map(fn (array $c, string $k) => "[$k] {$c['points']}/{$c['max']} — {$c['reason']}")
                ->implode(' | '),
            'priority' => $result->priority,
            'qualification_status' => $result->qualificationStatus,
            'qualified_at' => $result->qualificationStatus === 'qualificado' ? now() : null,
        ]);

        return $lead->fresh();
    }

    /**
     * Move o lead entre etapas do funil, registrando o histórico.
     */
    public function changeStage(Lead $lead, LeadStage $to, ?User $user = null, ?string $reason = null): Lead
    {
        if ($lead->stage === $to) {
            return $lead;
        }

        return DB::transaction(function () use ($lead, $to, $user, $reason) {
            $from = $lead->stage;
            $lead->update(['stage' => $to]);
            $lead->stageHistory()->create([
                'user_id' => $user?->id,
                'from_stage' => $from->value,
                'to_stage' => $to->value,
                'reason' => $reason,
            ]);
            AuditLogger::record('lead.stage_changed', $lead, ['from' => $from->value, 'to' => $to->value]);

            return $lead->fresh();
        });
    }

    /**
     * Revisão manual da qualificação.
     */
    public function review(Lead $lead, User $user, string $decision, ?string $notes = null): Lead
    {
        if (! in_array($decision, ['qualificado', 'precisa_evidencias', 'rejeitado'], true)) {
            throw ValidationException::withMessages(['decision' => 'Decisão de revisão inválida.']);
        }

        DB::transaction(function () use ($lead, $user, $decision, $notes) {
            $lead->update([
                'qualification_status' => $decision,
                'reviewed_at' => now(),
                'reviewed_by' => $user->id,
            ]);
            if ($notes) {
                $lead->notes()->create(['user_id' => $user->id, 'interaction_type' => 'nota', 'body' => $notes]);
            }
            AuditLogger::record('lead.reviewed', $lead, ['decision' => $decision]);
        });

        return $lead->fresh();
    }
}
