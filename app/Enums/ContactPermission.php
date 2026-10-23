<?php

declare(strict_types=1);

namespace App\Enums;

enum ContactPermission: string
{
    case Desconhecida = 'desconhecida';
    case Permitida = 'permitida';
    case Negada = 'negada';

    public function label(): string
    {
        return match ($this) {
            self::Desconhecida => 'Não verificada',
            self::Permitida => 'Permitida/opt-in',
            self::Negada => 'Negada/opt-out',
        };
    }
}
