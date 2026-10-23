<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20)->index();
            $table->string('provider', 40)->default('null');
            $table->string('model')->nullable();
            $table->string('status', 20)->default('indisponivel');
            $table->json('evidence_ids_used')->nullable()->comment('IDs de evidências vinculadas');
            $table->json('input_payload')->nullable()->comment('Dados mínimos enviados (sem segredos)');
            $table->json('structured_output')->nullable()->comment('Saída validada e estruturada');
            $table->json('uncertainties')->nullable()->comment('Incertezas sinalizadas');
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->decimal('cost_brl', 10, 4)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
    }
};
