<?php

declare(strict_types=1);

namespace App\Services\Communication;

use Illuminate\Support\Facades\Log;

/**
 * Instagram/Facebook via Meta Graph API. Somente endpoints, permissões e
 * tipos de conta autorizados pela Meta são utilizados. Um perfil público
 * não implica permissão para receber mensagens por API.
 */
class InstagramAdapter implements ChannelAdapterInterface
{
    public function key(): string
    {
        return 'instagram';
    }

    public function label(): string
    {
        return 'Instagram/Meta (Graph API)';
    }

    public function isConfigured(): bool
    {
        return filled(env('META_APP_ID')) && filled(env('META_PAGE_ACCESS_TOKEN'));
    }

    public function configurationHelp(): array
    {
        return [
            'Revise as permissões (instagram_manage_messages, pages_messaging) e o processo de revisão do app Meta.',
            'Defina META_APP_ID, META_APP_SECRET e META_PAGE_ACCESS_TOKEN no .env.',
            'Confirme que a conta pode receber mensagens pela API; perfis públicos não bastam.',
        ];
    }

    public function send(OutboundMessage $message): SendResult
    {
        if (! $this->isConfigured()) {
            return new SendResult(false, 'falha', null, null, 'Conector Instagram/Meta não configurado.');
        }

        // Postura conservadora: sem opt-in explicitamente verificado no
        // contato, o envio é bloqueado mesmo com credenciais presentes.
        $contact = $message->lead->contacts
            ->firstWhere('channel', 'instagram');

        if (! $contact || $contact->permission_status->value !== 'permitida') {
            Log::info('Instagram: envio bloqueado por falta de opt-in verificado', ['lead' => $message->lead->id]);
            return new SendResult(false, 'falha', null, null, 'Opt-in do canal Instagram não verificado. Envio bloqueado.');
        }

        // A chamada real exige app Meta revisado e recipient ig_id válido;
        // fica desativada até a conclusão da revisão oficial.
        return new SendResult(false, 'falha', null, null, 'Endpoints de envio Instagram aguardando revisão/autorização Meta.');
    }
}
