<?php

declare(strict_types=1);

namespace App\Enums;

enum MessageStatus: string
{
    case Fila = 'na_fila';
    case Enviada = 'enviada';
    case Entregue = 'entregue';
    case Respondida = 'respondida';
    case Falha = 'falha';
    case Rejeitada = 'rejeitada';
    case OptOut = 'opt_out';

    public function label(): string
    {
        return match ($this) {
            self::Fila => 'Na fila',
            self::Enviada => 'Enviada',
            self::Entregue => 'Entregue',
            self::Respondida => 'Respondida',
            self::Falha => 'Falha no envio',
            self::Rejeitada => 'Rejeitada pelo provedor',
            self::OptOut => 'Descadastro/opt-out',
        };
    }
}
