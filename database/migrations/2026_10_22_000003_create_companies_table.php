<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trade_name')->nullable();
            $table->string('segment')->nullable()->index();
            $table->string('city')->index();
            $table->string('state', 2)->default('PE');
            $table->string('website')->nullable();
            // Domínio normalizado (sem www/http). NULL quando inexistente:
            // o MySQL/SQLite permitem múltiplos NULL em índice único.
            $table->string('normalized_domain')->nullable()->unique();
            // Identificador estável do Google Places quando conhecido.
            $table->string('place_id')->nullable()->unique();
            $table->string('legal_basis', 40)->default('legitimo_interesse');
            $table->string('processing_purpose')->default('Prospecção comercial de serviços de design e web');
            $table->boolean('is_demo')->default(false)->comment('Dados de demonstração (fictícios)');
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['city', 'segment']);
            $table->index('is_demo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
