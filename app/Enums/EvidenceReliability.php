<?php

declare(strict_types=1);

namespace App\Enums;

enum EvidenceReliability: string
{
    case Baixa = 'baixa';
    case Media = 'media';
    case Alta = 'alta';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function weight(): int
    {
        return match ($this) {
            self::Baixa => 1,
            self::Media => 2,
            self::Alta => 3,
        };
    }
}
