<?php

declare(strict_types=1);

namespace App\Services\Search;

use App\Enums\ContactPermission;
use App\Enums\RetentionCondition;
use App\Enums\SearchJobStatus;
use App\Models\Company;
use App\Models\CompanySource;
use App\Models\Lead;
use App\Models\SearchJob;
use App\Models\User;
use App\Services\Dedup\CompanyDeduplicator;
use App\Services\Leads\LeadService;
use App\Support\AuditLogger;
use App\Support\DomainNormalizer;
use Illuminate\Support\Facades\DB;
use Throwable;

class SearchOrchestrator
{
    public function __construct(
        private readonly SearchProviderManager $manager,
        private readonly CompanyDeduplicator $deduplicator,
        private readonly LeadService $leadService,
    ) {}

    public function createJob(User $user, array $attributes): SearchJob
    {
        $job = SearchJob::create([
            'user_id' => $user->id,
            'service_id' => $attributes['service_id'] ?? null,
            'provider' => $this->manager->make()->key(),
            'status' => SearchJobStatus::Queued,
            'segment' => $attributes['segment'] ?? 'Todos',
            'cities' => $attributes['cities'],
            'state' => $attributes['state'] ?? config('prospector.region.state'),
            'target_quantity' => $attributes['target_quantity'] ?? config('prospector.search.default_target'),
        ]);

        AuditLogger::record('search.created', $job, ['target' => $job->target_quantity]);

        return $job;
    }

    /**
     * Executado dentro do worker de fila. Nunca simula resultados.
     */
    public function run(SearchJob $job): SearchJob
    {
        $provider = $this->manager->make($job->provider);

        if (! $provider->isConfigured()) {
            return $this->finishUnconfigured($job, $provider);
        }

        $job->update(['status' => SearchJobStatus::Running, 'started_at' => now(), 'error_message' => null]);

        try {
            $criteria = new SearchCriteria(
                cities: $job->cities,
                state: $job->state,
                segment: $job->segment,
                serviceId: $job->service_id,
                targetQuantity: $job->target_quantity,
                serviceName: $job->service?->name,
            );

            $records = $provider->search($criteria);
            $counters = ['found' => $records->count(), 'duplicate' => 0, 'rejected' => 0, 'qualified' => 0];

            foreach ($records as $i => $record) {
                $result = $this->persistRecord($job, $record);
                $counters[$result]++;
                $job->update(['progress' => (int) round((($i + 1) / max(1, $records->count())) * 100)]);
            }

            $job->update([
                'status' => SearchJobStatus::Completed,
                'found_count' => $counters['found'],
                'duplicate_count' => $counters['duplicate'],
                'rejected_count' => $counters['rejected'],
                'qualified_count' => $counters['qualified'],
                'progress' => 100,
                'finished_at' => now(),
            ]);

            AuditLogger::record('search.completed', $job, $counters);
        } catch (Throwable $e) {
            $job->update([
                'status' => SearchJobStatus::Failed,
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);
            AuditLogger::record('search.failed', $job, ['error' => $e->getMessage()]);
        }

        return $job->fresh();
    }

    /**
     * Persiste um registro do provedor com retenção correta e deduplicação.
     */
    private function persistRecord(SearchJob $job, ProspectRecord $record): string
    {
        if (blank($record->companyName) || blank($record->externalId) && blank($record->website)) {
            return 'rejected';
        }

        return DB::transaction(function () use ($job, $record) {
            $existing = $this->deduplicator->findExisting($record->externalId, $record->website);
            if ($existing !== null) {
                // Apenas registra a fonte; não duplica empresa nem lead.
                CompanySource::firstOrCreate(
                    ['source_name' => $record->sourceName, 'external_id' => $record->externalId],
                    [
                        'company_id' => $existing->id,
                        'source_url' => $record->sourceUrl,
                        'retention_condition' => $record->permanentRetentionAllowed
                            ? RetentionCondition::Permanente->value
                            : RetentionCondition::Transitoria->value,
                        'metadata' => $record->transientData,
                        'collected_at' => now(),
                        'verified_at' => now(),
                        'retention_expires_at' => now()->addDays((int) config('prospector.retention.transient_place_data_days')),
                    ],
                );

                return 'duplicate';
            }

            $company = Company::create([
                'name' => $record->companyName,
                'segment' => $record->segment,
                'city' => $record->city,
                'state' => $record->state,
                'website' => $record->website,
                'normalized_domain' => DomainNormalizer::normalize($record->website),
                'place_id' => $record->externalId,
                'last_verified_at' => now(),
            ]);

            CompanySource::create([
                'company_id' => $company->id,
                'source_name' => $record->sourceName,
                'source_url' => $record->sourceUrl,
                'external_id' => $record->externalId,
                'retention_condition' => $record->permanentRetentionAllowed
                    ? RetentionCondition::Permanente->value
                    : RetentionCondition::Transitoria->value,
                'metadata' => $record->transientData,
                'collected_at' => now(),
                'verified_at' => now(),
                'retention_expires_at' => now()->addDays((int) config('prospector.retention.transient_place_data_days')),
                'is_primary' => true,
            ]);

            $lead = Lead::create([
                'company_id' => $company->id,
                'search_job_id' => $job->id,
                'service_id' => $job->service_id,
                'assigned_user_id' => $job->user_id,
                'stage' => 'pesquisado',
                'last_verified_at' => now(),
            ]);

            foreach ($record->contacts as $contact) {
                $lead->contacts()->create([
                    'channel' => $contact['channel'],
                    'value' => $contact['value'],
                    'permission_status' => ContactPermission::Desconhecida,
                    'verified_at' => now(),
                ]);
            }

            $lead = $this->leadService->applyScore($lead);

            return $lead->qualification_status->value === 'qualificado' ? 'qualified' : 'found';
        });
    }

    private function finishUnconfigured(SearchJob $job, SearchProviderInterface $provider): SearchJob
    {
        $job->update([
            'status' => SearchJobStatus::Failed,
            'error_message' => 'Conector não configurado. ' . implode(' ', $provider->configurationHelp()),
            'finished_at' => now(),
            'progress' => 0,
        ]);
        AuditLogger::record('search.unconfigured', $job);

        return $job->fresh();
    }
}
