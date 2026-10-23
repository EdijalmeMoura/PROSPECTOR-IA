<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Enums\AiKind;
use App\Enums\AiStatus;
use App\Models\AiGeneration;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Monta prompts apenas com dados/evidências verificadas, chama o provedor
 * configurado e persiste saídas estruturadas vinculadas às evidências.
 * A IA nunca recebe credenciais e não pode inventar contatos ou preços.
 */
class LeadAiService
{
    public function __construct(private readonly AiManager $manager) {}

    public function isAvailable(): bool
    {
        return $this->manager->make()->isConfigured();
    }

    public function generate(Lead $lead, AiKind $kind, User $user): AiGeneration
    {
        $provider = $this->manager->make();
        $evidence = $lead->evidence()->latest('observed_at')->limit(10)->get();
        $evidenceIds = $evidence->pluck('id')->all();

        $context = $this->buildContext($lead, $evidence->toArray());
        $instruction = $this->instructionFor($kind);

        $generation = new AiGeneration([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'kind' => $kind,
            'provider' => $provider->key(),
            'model' => config('prospector.ai.model'),
            'status' => AiStatus::Indisponivel,
            'evidence_ids_used' => $evidenceIds,
            'input_payload' => $context,
        ]);

        if (! $provider->isConfigured()) {
            $generation->fill([
                'status' => AiStatus::Indisponivel,
                'error_message' => 'Provedor de IA não configurado. A revisão manual continua disponível.',
                'uncertainties' => $provider->configurationHelp(),
            ]);
            $generation->save();

            return $generation;
        }

        $response = $provider->generate(new AiPrompt($kind, $instruction, $context, $evidenceIds));

        $generation->fill([
            'status' => $response->success ? AiStatus::Sucesso : AiStatus::Falha,
            'structured_output' => $response->success ? $response->structured : null,
            'uncertainties' => $response->uncertainties,
            'prompt_tokens' => $response->promptTokens,
            'completion_tokens' => $response->completionTokens,
            'error_message' => $response->error,
        ]);
        $generation->save();

        return $generation;
    }

    /**
     * @param  array<int, mixed>  $evidence
     * @return array<string, mixed>
     */
    private function buildContext(Lead $lead, array $evidence): array
    {
        return [
            'empresa' => [
                'nome' => $lead->company->name,
                'cidade' => $lead->company->city,
                'estado' => $lead->company->state,
                'segmento' => $lead->company->segment,
                'site' => $lead->company->website,
            ],
            'servico_recomendado' => $lead->service?->name,
            'evidencias_verificadas' => array_map(fn (array $e) => [
                'tipo' => $e['type'],
                'descricao' => $e['description'],
                'fonte' => $e['source_url'],
                'observada_em' => optional($e['observed_at'] ?? null)->format('Y-m-d'),
                'confiabilidade' => $e['reliability'],
            ], $evidence),
            'observacao' => 'Use somente as evidências fornecidas. Não invente dados, contatos, preços ou intenção de compra.',
        ];
    }

    private function instructionFor(AiKind $kind): string
    {
        $base = 'Você é um assistente de prospecção comercial. Responda em JSON, em português brasileiro. '
            .'Use exclusivamente as evidências apresentadas; sinalize em "incertezas" o que não for verificável. '
            .'Não invente contatos, preços, faturamento, orçamento ou intenção de compra.';

        return match ($kind) {
            AiKind::Resumo => $base.' Resuma a empresa com base nas evidências. Chaves: "resumo", "incertezas".',
            AiKind::Oportunidade => $base.' Identifique oportunidades comerciais plausíveis. Chaves: "oportunidades" (array), "servico_recomendado", "incertezas".',
            AiKind::Mensagem => $base.' Redija uma mensagem comercial curta, educada e sem promessas não verificáveis. Chaves: "mensagem", "incertezas".',
            AiKind::Proposta => $base.' Sugira uma proposta e próximo passo, sem definir valores não fornecidos. Chaves: "proposta", "proximo_passo", "incertezas".',
        };
    }
}
