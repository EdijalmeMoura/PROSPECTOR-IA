<?php

declare(strict_types=1);

use App\Http\Controllers\AiGenerationController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CsvImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadNoteController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OptOutController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\SearchJobController;
use App\Http\Controllers\Settings\ConnectorController;
use App\Http\Controllers\Settings\ServiceController;
use App\Http\Controllers\Settings\UserController;
use App\Http\Controllers\AuditLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/painel', [DashboardController::class, 'index'])->name('dashboard');

    // Leads / CRM
    Route::resource('leads', LeadController::class)->except(['destroy']);
    Route::patch('leads/{lead}/stage', [LeadController::class, 'changeStage'])->name('leads.stage');
    Route::patch('leads/{lead}/review', [LeadController::class, 'review'])->name('leads.review');
    Route::get('leads/{lead}/export', [LeadController::class, 'exportShow'])->name('leads.export-show');
    Route::get('leads-export', [LeadController::class, 'export'])->name('leads.export');
    Route::post('leads/{lead}/notes', [LeadNoteController::class, 'store'])->name('leads.notes.store');
    Route::post('leads/{lead}/ai', [AiGenerationController::class, 'generate'])->name('leads.ai.generate');

    // Empresas
    Route::resource('companies', CompanyController::class)->except(['destroy']);

    // Pesquisas
    Route::resource('searches', SearchJobController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('searches/{search}/reprocess', [SearchJobController::class, 'reprocess'])->name('searches.reprocess');

    // CSV
    Route::get('/csv', [CsvImportController::class, 'create'])->name('csv.create');
    Route::post('/csv/preview', [CsvImportController::class, 'preview'])->name('csv.preview');
    Route::post('/csv/import', [CsvImportController::class, 'store'])->name('csv.import');

    // Aprovação humana e mensagens
    Route::resource('approvals', ApprovalController::class)->only(['index', 'show', 'store']);
    Route::patch('approvals/{approval}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::patch('approvals/{approval}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    Route::patch('approvals/{approval}/cancel', [ApprovalController::class, 'cancel'])->name('approvals.cancel');
    Route::post('approvals/{approval}/send', [MessageController::class, 'send'])->name('approvals.send');

    // Propostas e negócios
    Route::resource('proposals', ProposalController::class);
    Route::resource('deals', DealController::class)->only(['index', 'show', 'update']);

    // Opt-outs
    Route::resource('opt-outs', OptOutController::class)->only(['index', 'store']);

    // Administração
    Route::middleware('role:admin')->group(function () {
        Route::resource('services', ServiceController::class)->except(['show', 'destroy']);
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::patch('users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
        Route::get('conectores', [ConnectorController::class, 'show'])->name('connectors.show');
        Route::get('auditoria', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});
