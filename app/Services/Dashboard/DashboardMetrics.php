<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\DealStatus;
use App\Enums\MessageStatus;
use App\Enums\SearchJobStatus;
use App\Models\Approval;
use App\Models\Company;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\MessageLog;
use App\Models\Proposal;
use App\Models\SearchJob;

/**
 * Métricas calculadas exclusivamente a partir de registros reais.
 * Valores previstos nunca são somados à receita recebida.
 */
class DashboardMetrics
{
    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        return [
            'searches_started' => SearchJob::count(),
            'searches_completed' => SearchJob::where('status', SearchJobStatus::Completed->value)->count(),
            'searches_failed' => SearchJob::where('status', SearchJobStatus::Failed->value)->count(),
            'companies_found' => Company::real()->count(),
            'leads_qualified' => Lead::real()->where('qualification_status', 'qualificado')->count(),
            'approvals_pending' => Approval::where('status', 'pendente')->count(),
            'messages' => [
                'approved' => Approval::where('status', 'aprovada')->count(),
                'sent' => MessageLog::where('status', MessageStatus::Enviada->value)->count(),
                'delivered' => MessageLog::where('status', MessageStatus::Entregue->value)->count(),
                'replied' => MessageLog::where('status', MessageStatus::Respondida->value)->count(),
                'failed' => MessageLog::where('status', MessageStatus::Falha->value)->count(),
            ],
            'proposals_sent' => Proposal::whereNotNull('sent_at')->count(),
            'deals_won' => Deal::where('status', DealStatus::Ganho->value)->count(),
            'deals_lost' => Deal::where('status', DealStatus::Perdido->value)->count(),
            'expected_amount' => (float) Deal::where('status', DealStatus::Aberto->value)->sum('expected_amount'),
            'received_amount' => (float) Deal::sum('received_amount'),
            'by_city' => $this->group(Lead::query()->join('companies', 'companies.id', '=', 'leads.company_id')
                ->selectRaw('companies.city as label, count(*) as total')->where('leads.is_demo', false)->groupBy('label')->orderByDesc('total')->limit(8)->get()),
            'by_priority' => $this->group(Lead::real()->selectRaw('priority as label, count(*) as total')->groupBy('label')->get()),
            'by_stage' => $this->group(Lead::real()->selectRaw('stage as label, count(*) as total')->groupBy('label')->orderByDesc('total')->get()),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $rows
     * @return array<int, array{label:string, total:int}>
     */
    private function group($rows): array
    {
        return $rows->map(fn ($r) => ['label' => (string) $r->label, 'total' => (int) $r->total])->all();
    }
}
