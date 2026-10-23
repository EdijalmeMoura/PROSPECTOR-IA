<?php

declare(strict_types=1);

namespace App\Enums;

enum SearchJobStatus: string
{
    case Queued = 'queued';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Queued => 'Na fila',
            self::Running => 'Em execução',
            self::Completed => 'Concluída',
            self::Failed => 'Falhou',
            self::Cancelled => 'Cancelada',
        };
    }

    public function isFinished(): bool
    {
        return in_array($this, [self::Completed, self::Failed, self::Cancelled], true);
    }
}
