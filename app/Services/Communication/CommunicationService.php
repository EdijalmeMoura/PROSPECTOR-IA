<?php

declare(strict_types=1);

namespace App\Services\Communication;

use App\Enums\ApprovalStatus;
use App\Enums\ChannelType;
use App\Enums\MessageStatus;
use App\Models\Approval;
use App\Models\MessageLog;
use App\Models\OptOut;
use App\Support\AuditLogger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Único ponto de saída de mensagens externas. Aprovação humana é obrigatória
 * e vinculada a destinatário, canal e conteúdo exatos. Nada é marcado como
 * enviado sem confirmação do provedor.
 */
class CommunicationService
{
    /**
     * @return array{ok:bool, reason?:string, log?:MessageLog}
     */
    public function send(Approval $approval): array
    {
        $approval->loadMissing('lead.contacts');

        $guard = $this->guard($approval);
        if ($guard !== null) {
            return ['ok' => false, 'reason' => $guard];
        }

        $adapter = $this->adapterFor($approval->channel);
        if (! $adapter->isConfigured()) {
            $reason = 'Canal não configurado. Pendente: ' . implode('; ', $adapter->configurationHelp());
            $log = $this->recordBlockedAttempt($approval, $reason);

            return ['ok' => false, 'reason' => $reason, 'log' => $log];
        }

        $message = new OutboundMessage(
            lead: $approval->lead,
            approval: $approval,
            channel: $approval->channel->value,
            recipient: $approval->recipient,
            text: $approval->full_text,
        );

        $result = $adapter->send($message);

        $log = DB::transaction(function () use ($approval, $result) {
            $log = MessageLog::create([
                'lead_id' => $approval->lead_id,
                'approval_id' => $approval->id,
                'channel' => $approval->channel->value,
                'recipient' => $approval->recipient,
                'status' => $result->success ? MessageStatus::Enviada->value : MessageStatus::Falha->value,
                'provider_message_id' => $result->providerMessageId,
                'content_snapshot' => $approval->full_text,
                'provider_payload' => $result->payload,
                'error_message' => $result->error,
                'sent_at' => $result->success ? now() : null,
            ]);

            AuditLogger::record('message.' . ($result->success ? 'sent' : 'failed'), $log, [
                'channel' => $approval->channel->value,
                'approval_id' => $approval->id,
            ]);

            return $log;
        });

        return $result->success
            ? ['ok' => true, 'log' => $log]
            : ['ok' => false, 'reason' => $result->error ?? 'Falha não especificada.', 'log' => $log];
    }

    /**
     * Verifica todas as condições de segurança antes do envio.
     */
    public function guard(Approval $approval): ?string
    {
        if (! config('prospector.communication.human_approval_required', true)) {
            return 'Aprovação humana obrigatória está desabilitada (configuração inválida).';
        }

        if ($approval->status !== ApprovalStatus::Aprovada) {
            return 'Aprovação inexistente ou não está no status "aprovada".';
        }

        if ($approval->approved_at === null) {
            return 'A data de aprovação não foi registrada.';
        }

        $expectedHash = Approval::hashContent(
            $approval->channel,
            $approval->recipient,
            $approval->full_text,
        );
        if (! hash_equals($expectedHash, $approval->content_hash)) {
            return 'O conteúdo foi alterado após a aprovação; nova versão e aprovação são necessárias.';
        }

        if (OptOut::where('channel', $approval->channel->value)
            ->where('identifier', $this->normalizeIdentifier($approval->channel, $approval->recipient))
            ->exists()) {
            return 'Existe um pedido de não contato (opt-out) para este canal/destinatário.';
        }

        $todayCount = MessageLog::where('channel', $approval->channel->value)
            ->whereDate('created_at', Carbon::today())
            ->count();
        if ($todayCount >= (int) config('prospector.communication.daily_message_limit', 100)) {
            return 'Limite diário de mensagens atingido; envio bloqueado até amanhã.';
        }

        return null;
    }

    private function recordBlockedAttempt(Approval $approval, string $reason): MessageLog
    {
        return DB::transaction(function () use ($approval, $reason) {
            $log = MessageLog::create([
                'lead_id' => $approval->lead_id,
                'approval_id' => $approval->id,
                'channel' => $approval->channel->value,
                'recipient' => $approval->recipient,
                'status' => MessageStatus::Falha->value,
                'content_snapshot' => $approval->full_text,
                'error_message' => $reason,
            ]);

            AuditLogger::record('message.blocked', $log, ['reason' => $reason]);

            return $log;
        });
    }

    private function adapterFor(ChannelType $channel): ChannelAdapterInterface
    {
        return match ($channel) {
            ChannelType::Email => app(EmailAdapter::class),
            ChannelType::WhatsApp => app(WhatsAppAdapter::class),
            ChannelType::Instagram => app(InstagramAdapter::class),
        };
    }

    private function normalizeIdentifier(ChannelType $channel, string $recipient): string
    {
        return $channel === ChannelType::WhatsApp
            ? preg_replace('/\D+/', '', $recipient) ?? $recipient
            : mb_strtolower(trim($recipient));
    }
}
