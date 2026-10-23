<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\RetentionCondition;
use App\Models\CompanySource;
use App\Models\MessageLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Aplica a política de retenção/LGPD:
 *  - remove metadados transitórios vencidos do Google Places (place_id é mantido);
 *  - anonimiza logs de mensagem além do prazo configurado.
 */
class PurgeRetentionData extends Command
{
    protected $signature = 'prospector:purge-retention {--dry-run : apenas relata o que seria removido}';

    protected $description = 'Aplica a política de retenção de dados (LGPD)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $expiredSources = CompanySource::query()
            ->where('retention_condition', RetentionCondition::Transitoria->value)
            ->whereNotNull('retention_expires_at')
            ->where('retention_expires_at', '<', now());

        $expiredMessages = MessageLog::query()
            ->where('created_at', '<', now()->subMonths((int) config('prospector.retention.message_log_months')));

        $this->info("Fontes transitórias vencidas: {$expiredSources->count()}");
        $this->info("Logs de mensagem fora do prazo: {$expiredMessages->count()}");

        if ($dryRun) {
            $this->warn('Modo dry-run: nenhuma alteração foi feita.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($expiredSources, $expiredMessages) {
            // Limpa apenas o conteúdo transitório; mantém fonte, external_id (place_id) e vínculos.
            $expiredSources->update(['metadata' => null]);
            $expiredMessages->update([
                'recipient' => 'anonimizado',
                'content_snapshot' => 'conteúdo removido pela política de retenção',
                'provider_payload' => null,
            ]);
        });

        $this->info('Retenção aplicada com sucesso.');

        return self::SUCCESS;
    }
}
