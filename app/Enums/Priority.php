<?php

declare(strict_types=1);

namespace App\Enums;

enum Priority: string
{
    case Alta = 'alta';
    case Media = 'media';
    case Baixa = 'baixa';
    case SemEvidencias = 'sem_evidencias';

    public function label(): string
    {
        return match ($this) {
            self::Alta => 'Prioridade alta',
            self::Media => 'Prioridade média',
            self::Baixa => 'Prioridade baixa',
            self::SemEvidencias => 'Precisa de mais evidências',
        };
    }
}
