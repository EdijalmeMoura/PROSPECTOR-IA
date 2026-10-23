<?php

declare(strict_types=1);

use App\Http\Controllers\Webhooks\MetaWebhookController;
use Illuminate\Support\Facades\Route;

/*
 * Webhooks autenticados dos provedores de comunicação.
 * Estão fora do grupo "web" (sem estado/sessão) e isentos de CSRF; a
 * autenticidade é verificada por token de verificação/assinatura.
 */
Route::prefix('webhooks')->group(function () {
    Route::get('meta', [MetaWebhookController::class, 'verify'])->name('webhooks.meta.verify');
    Route::post('meta', [MetaWebhookController::class, 'handle'])->name('webhooks.meta.handle');
});
