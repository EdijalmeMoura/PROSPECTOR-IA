<?php

declare(strict_types=1);

namespace App\Services\Approval;

use App\Enums\ApprovalStatus;
use App\Enums\ChannelType;
use App\Enums\ContactPermission;
use App\Models\Approval;
use App\Models\Lead;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalService
{
    /**
     * Cria uma nova versão de conteúdo para aprovação. Versões anteriores
     * pendentes são substituídas e qualquer aprovação vinculada ao conteúdo
     * antigo perde efeito (nova edição = nova versão obrigatória).
     */
    public function createDraft(
        Lead $lead,
        ChannelType $channel,
        string $recipient,
        string $text,
        ?int $aiGenerationId = null,
    ): Approval {
        return DB::transaction(function () use ($lead, $channel, $recipient, $text, $aiGenerationId) {
            $previousVersion = (int) Approval::where('lead_id', $lead->id)->max('content_version');

            Approval::where('lead_id', $lead->id)
                ->whereIn('status', [ApprovalStatus::Pendente->value, ApprovalStatus::Aprovada->value])
                ->update(['status' => ApprovalStatus::Substituida->value]);

            $permission = $lead->contacts
                ->where('channel', $channel->value)
                ->sortByDesc(fn ($c) => $c->permission_status === ContactPermission::Permitida)
                ->first()?->permission_status ?? ContactPermission::Desconhecida;

            $approval = Approval::create([
                'lead_id' => $lead->id,
                'ai_generation_id' => $aiGenerationId,
                'channel' => $channel,
                'recipient' => trim($recipient),
                'content_version' => $previousVersion + 1,
                'full_text' => $text,
                'evidence_snapshot' => $lead->evidence->map(fn ($e) => [
                    'id' => $e->id,
                    'type' => $e->type,
                    'description' => $e->description,
                    'source_url' => $e->source_url,
                ])->all(),
                'channel_permission' => $permission,
                'status' => ApprovalStatus::Pendente,
                'content_hash' => Approval::hashContent($channel, trim($recipient), $text),
            ]);

            AuditLogger::record('approval.created', $approval, ['version' => $approval->content_version]);

            return $approval;
        });
    }

    public function approve(Approval $approval, User $user): Approval
    {
        if ($approval->status !== ApprovalStatus::Pendente) {
            throw ValidationException::withMessages(['approval' => 'Apenas solicitações pendentes podem ser aprovadas.']);
        }

        DB::transaction(function () use ($approval, $user) {
            $approval->update([
                'status' => ApprovalStatus::Aprovada,
                'approver_id' => $user->id,
                'approved_at' => now(),
                'decision_note' => null,
            ]);
            AuditLogger::record('approval.approved', $approval, ['approver' => $user->id]);
        });

        return $approval->fresh();
    }

    public function reject(Approval $approval, User $user, string $note): Approval
    {
        if ($approval->status !== ApprovalStatus::Pendente) {
            throw ValidationException::withMessages(['approval' => 'Apenas solicitações pendentes podem ser rejeitadas.']);
        }

        DB::transaction(function () use ($approval, $user, $note) {
            $approval->update([
                'status' => ApprovalStatus::Rejeitada,
                'approver_id' => $user->id,
                'rejected_at' => now(),
                'decision_note' => $note,
            ]);
            AuditLogger::record('approval.rejected', $approval, ['note' => $note]);
        });

        return $approval->fresh();
    }

    public function cancel(Approval $approval, User $user): Approval
    {
        if (in_array($approval->status, [ApprovalStatus::Cancelada, ApprovalStatus::Substituida], true)) {
            return $approval;
        }

        DB::transaction(function () use ($approval, $user) {
            $approval->update(['status' => ApprovalStatus::Cancelada]);
            AuditLogger::record('approval.cancelled', $approval, ['user' => $user->id]);
        });

        return $approval->fresh();
    }
}
