<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadStage: string
{
    case Novo = 'novo';
    case Pesquisado = 'pesquisado';
    case Qualificado = 'qualificado';
    case AguardandoAprovacao = 'aguardando_aprovacao';
    case Contatado = 'contatado';
    case Respondeu = 'respondeu';
    case PropostaEnviada = 'proposta_enviada';
    case Negociacao = 'negociacao';
    case Ganho = 'ganho';
    case Perdido = 'perdido';

    public function label(): string
    {
        return ucfirst(str_replace('_', ' ', $this->value));
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $c) => ['value' => $c->value, 'label' => $c->label()], self::cases());
    }
}
