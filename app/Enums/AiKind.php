<?php

declare(strict_types=1);

namespace App\Enums;

enum AiKind: string
{
    case Resumo = 'resumo';
    case Oportunidade = 'oportunidade';
    case Mensagem = 'mensagem';
    case Proposta = 'proposta';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
