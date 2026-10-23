<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('search_job_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('stage', 30)->default('novo')->index();
            $table->string('qualification_status', 30)->default('pendente')->index();
            $table->string('priority', 20)->default('sem_evidencias');
            $table->unsignedTinyInteger('score')->default(0)->comment('Pontuação 0-100');
            $table->json('score_breakdown')->nullable()->comment('Notas por critério');
            $table->text('score_justification')->nullable();
            $table->text('recommended_service_reason')->nullable();
            $table->text('opportunity_evidence_summary')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamp('qualified_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['stage', 'priority']);
            $table->index('is_demo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
