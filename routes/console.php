<?php

declare(strict_types=1);

use App\Console\Commands\PurgeRetentionData;
use Illuminate\Support\Facades\Schedule;

// Limpeza diária de dados transitórios/fora do prazo de retenção (LGPD).
Schedule::command(PurgeRetentionData::class)->dailyAt('02:30');

// Processamento de filas em hospedagem sem supervisor:
// ver README — alternativa ao worker contínuo usando o cron do sistema.
Schedule::command('queue:work --stop-when-empty --max-time=60 --tries=2')
    ->everyMinute()
    ->withoutOverlapping();
