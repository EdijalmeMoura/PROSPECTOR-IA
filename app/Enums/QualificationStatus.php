<?php

declare(strict_types=1);

namespace App\Enums;

enum QualificationStatus: string
{
    case Pendente = 'pendente';
    case Qualificado = 'qualificado';
    case PrecisaEvidencias = 'precisa_evidencias';
    case Rejeitado = 'rejeitado';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente',
            self::Qualificado => 'Qualificado',
            self::PrecisaEvidencias => 'Precisa de mais evidências',
            self::Rejeitado => 'Rejeitado na revisão',
        };
    }
}
