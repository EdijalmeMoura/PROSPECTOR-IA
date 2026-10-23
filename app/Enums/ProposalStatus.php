<?php

declare(strict_types=1);

namespace App\Enums;

enum ProposalStatus: string
{
    case Rascunho = 'rascunho';
    case Enviada = 'enviada';
    case Aceita = 'aceita';
    case Recusada = 'recusada';
    case Expirada = 'expirada';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
