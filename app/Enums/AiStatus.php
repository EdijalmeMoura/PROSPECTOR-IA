<?php

declare(strict_types=1);

namespace App\Enums;

enum AiStatus: string
{
    case Sucesso = 'sucesso';
    case Falha = 'falha';
    case Indisponivel = 'indisponivel';

    public function label(): string
    {
        return ucfirst(str_replace('_', ' ', $this->value));
    }
}
