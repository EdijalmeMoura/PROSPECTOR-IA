<?php

declare(strict_types=1);

namespace App\Services\Scoring;

use App\Enums\ContactPermission;
use App\Enums\EvidenceReliability;
use App\Enums\Priority;
use App\Enums\QualificationStatus;
use App\Models\Lead;
use App\Models\LeadEvidence;

/**
 * Pontuação explicável de 0 a 100.
 * Os pesos e limites vêm de config/prospector.php e são auditáveis.
 * Nenhuma nota infere intenção de compra, faturamento ou orçamento.
 */
class LeadScorer
{
    /** Tipos de evidência que sinalizam necessidade observável de design/site. */
    private const DESIGN_NEED_TYPES = [
        'site_ausente', 'site_desatualizado', 'identidade_visual_fraca',
        'redes_sem_padrao', 'wordpress_lento', 'wordpress_erro', 'sem_redes_sociais',
    ];

    public function score(Lead $lead): ScoreResult
    {
        $weights = config('prospector.scoring.weights');
        $thresholds = config('prospector.scoring.thresholds');

        $criteria = [
            'design_need' => $this->designNeed($lead, (int) $weights['design_need']),
            'service_fit' => $this->serviceFit($lead, (int) $weights['service_fit']),
            'evidence_reliability' => $this->evidenceReliability($lead, (int) $weights['evidence_reliability']),
            'channel_available' => $this->channelAvailable($lead, (int) $weights['channel_available']),
            'regional_fit' => $this->regionalFit($lead, (int) $weights['regional_fit']),
        ];

        $total = array_sum(array_map(fn (array $c) => $c['points'], $criteria));
        $total = max(0, min(100, $total));

        [$priority, $status] = $this->classify($total, $thresholds);

        $summary = sprintf(
            'Pontuação %d/100 — %s. Detalhamento: %s',
            $total,
            $this->priorityLabel($priority),
            collect($criteria)->map(fn (array $c, string $k) => "{$k}={$c['points']}")->implode(', ')
        );

        return new ScoreResult($total, $criteria, $priority, $status, $summary);
    }

    /** @return array{points:int, max:int, reason:string} */
    private function designNeed(Lead $lead, int $max): array
    {
        $evidence = $lead->evidence;
        $designEvidence = $evidence->filter(fn (LeadEvidence $e) => in_array($e->type, self::DESIGN_NEED_TYPES, true));

        if ($designEvidence->isEmpty()) {
            return ['points' => 0, 'max' => $max, 'reason' => 'Nenhuma evidência observável de necessidade de design ou melhoria de site foi registrada.'];
        }

        // Até 2 evidências distintas contribuem; a qualidade eleva a nota.
        $distinct = $designEvidence->unique('type')->take(2)->count();
        $base = $distinct >= 2 ? 22 : 14;
        $reliabilityBonus = $designEvidence->contains(fn (LeadEvidence $e) => $e->reliability === EvidenceReliability::Alta) ? 8 : 4;

        return [
            'points' => min($max, $base + $reliabilityBonus),
            'max' => $max,
            'reason' => sprintf('%d evidência(s) observável(is) de necessidade de design/site: %s.',
                $designEvidence->count(),
                $designEvidence->pluck('type')->unique()->implode(', ')
            ),
        ];
    }

    /** @return array{points:int, max:int, reason:string} */
    private function serviceFit(Lead $lead, int $max): array
    {
        if ($lead->service === null) {
            return ['points' => 0, 'max' => $max, 'reason' => 'Nenhum serviço do catálogo foi recomendado ainda.'];
        }

        $points = 15;
        $reason = "Serviço recomendado compatível com o catálogo: {$lead->service->name}.";

        if ($lead->recommended_service_reason) {
            $points += 10;
            $reason .= ' Justificativa vinculada a evidências.';
        }

        return ['points' => min($max, $points), 'max' => $max, 'reason' => $reason];
    }

    /** @return array{points:int, max:int, reason:string} */
    private function evidenceReliability(Lead $lead, int $max): array
    {
        $evidence = $lead->evidence;
        if ($evidence->isEmpty()) {
            return ['points' => 0, 'max' => $max, 'reason' => 'Sem evidências registradas; não há como atestar confiabilidade.'];
        }

        $avgReliability = $evidence->avg(fn (LeadEvidence $e) => $e->reliability->weight()) / 3;
        $points = (int) round(12 * $avgReliability);

        $fresh = $evidence->filter(fn (LeadEvidence $e) => $e->observed_at !== null && $e->observed_at->greaterThan(now()->subDays(90)));
        if ($fresh->isNotEmpty()) {
            $points += 8;
            $reason = 'Evidências com confiabilidade média e observação nos últimos 90 dias.';
        } else {
            $reason = 'Evidências presentes, mas sem observação recente (mais de 90 dias).';
        }

        return ['points' => min($max, $points), 'max' => $max, 'reason' => $reason];
    }

    /** @return array{points:int, max:int, reason:string} */
    private function channelAvailable(Lead $lead, int $max): array
    {
        $contacts = $lead->contacts;
        if ($contacts->isEmpty()) {
            return ['points' => 0, 'max' => $max, 'reason' => 'Nenhum canal comercial cadastrado.'];
        }

        $allowed = $contacts->where('permission_status', ContactPermission::Permitida);
        if ($allowed->isNotEmpty()) {
            return [
                'points' => $max,
                'max' => $max,
                'reason' => 'Canal com permissão/opt-in verificada: ' . $allowed->pluck('channel')->unique()->implode(', ') . '.',
            ];
        }

        $unknown = $contacts->where('permission_status', ContactPermission::Desconhecida);
        if ($unknown->isNotEmpty()) {
            return [
                'points' => (int) round($max * 0.4),
                'max' => $max,
                'reason' => 'Canal disponível, mas permissão de contato ainda não verificada (' . $unknown->pluck('channel')->unique()->implode(', ') . ').',
            ];
        }

        return ['points' => 0, 'max' => $max, 'reason' => 'Os canais conhecidos estão marcados como negados/opt-out.'];
    }

    /** @return array{points:int, max:int, reason:string} */
    private function regionalFit(Lead $lead, int $max): array
    {
        $city = $lead->company?->city;
        if ($city === null) {
            return ['points' => 0, 'max' => $max, 'reason' => 'Cidade não informada.'];
        }

        if (in_array($city, config('prospector.region.cities'), true)) {
            return ['points' => $max, 'max' => $max, 'reason' => "Empresa em {$city}/{$lead->company->state}, dentro da região de atuação configurada."];
        }

        if (($lead->company?->state) === config('prospector.region.state')) {
            return ['points' => (int) round($max / 2), 'max' => $max, 'reason' => "Empresa em {$city}, no mesmo estado, mas fora da região metropolitana prioritária."];
        }

        return ['points' => 0, 'max' => $max, 'reason' => "Empresa fora do estado de atuação ({$city})."];
    }

    /**
     * @return array{0:string, 1:string}
     */
    private function classify(int $total, array $thresholds): array
    {
        if ($total >= $thresholds['high']) {
            return [Priority::Alta->value, QualificationStatus::Qualificado->value];
        }
        if ($total >= $thresholds['medium']) {
            return [Priority::Media->value, QualificationStatus::Qualificado->value];
        }

        return [Priority::SemEvidencias->value, QualificationStatus::PrecisaEvidencias->value];
    }

    private function priorityLabel(string $priority): string
    {
        return Priority::tryFrom($priority)?->label() ?? $priority;
    }
}
