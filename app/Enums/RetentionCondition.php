<?php

declare(strict_types=1);

namespace App\Enums;

enum RetentionCondition: string
{
    case Permanente = 'permanente';      // place_id ou dados de fonte independente autorizada
    case Transitoria = 'transitoria';    // conteúdo do Places sujeito às regras do Google
    case Anonimizada = 'anonimizada';

    public function label(): string
    {
        return match ($this) {
            self::Permanente => 'Retenção permanente permitida',
            self::Transitoria => 'Retenção transitória (Google Places)',
            self::Anonimizada => 'Dados anonimizados',
        };
    }
}
