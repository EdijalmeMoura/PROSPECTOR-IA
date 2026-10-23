<?php

declare(strict_types=1);

namespace App\Services\Communication;

use App\Mail\LeadOutreachMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailAdapter implements ChannelAdapterInterface
{
    public function key(): string
    {
        return 'email';
    }

    public function label(): string
    {
        return 'E-mail (SMTP/API autenticada)';
    }

    public function isConfigured(): bool
    {
        $mailer = (string) config('mail.default', 'log');

        return ! in_array($mailer, ['log', 'array', ''], true);
    }

    public function configurationHelp(): array
    {
        return [
            'Configure MAIL_MAILER com um transporte real (smtp, ses, mailgun, postmark, resend).',
            'Preencha MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD e remetente verificado.',
            'Processe rejeições e descadastros e respeite a legislação antispam.',
        ];
    }

    public function send(OutboundMessage $message): SendResult
    {
        try {
            $mail = new LeadOutreachMail($message->text, $message->lead);
            Mail::to($message->recipient)->send($mail);

            return new SendResult(true, 'enviada', null, ['driver' => config('mail.default')]);
        } catch (\Throwable $e) {
            Log::error('E-mail: falha no envio', ['lead' => $message->lead->id, 'error' => $e->getMessage()]);

            return new SendResult(false, 'falha', null, null, $e->getMessage());
        }
    }
}
