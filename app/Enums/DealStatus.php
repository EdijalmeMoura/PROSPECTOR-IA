<?php

declare(strict_types=1);

namespace App\Enums;

enum DealStatus: string
{
    case Aberto = 'aberto';
    case Ganho = 'ganho';
    case Perdido = 'perdido';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
