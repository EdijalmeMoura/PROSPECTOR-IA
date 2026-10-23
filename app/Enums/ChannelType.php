<?php

declare(strict_types=1);

namespace App\Enums;

enum ChannelType: string
{
    case Email = 'email';
    case WhatsApp = 'whatsapp';
    case Instagram = 'instagram';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'E-mail',
            self::WhatsApp => 'WhatsApp',
            self::Instagram => 'Instagram',
        };
    }
}
