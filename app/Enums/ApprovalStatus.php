<?php

declare(strict_types=1);

namespace App\Enums;

enum ApprovalStatus: string
{
    case Pendente = 'pendente';
    case Aprovada = 'aprovada';
    case Rejeitada = 'rejeitada';
    case Cancelada = 'cancelada';
    case Substituida = 'substituida';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
