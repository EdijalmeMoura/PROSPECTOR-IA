<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_generation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('channel', 20);
            $table->string('recipient');
            $table->unsignedInteger('content_version')->default(1);
            $table->longText('full_text');
            $table->json('evidence_snapshot')->nullable();
            $table->string('channel_permission', 20)->default('desconhecida');
            $table->string('status', 20)->default('pendente')->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('decision_note')->nullable();
            // Hash do conteúdo exato: vincula aprovação a destinatário+canal+texto.
            $table->string('content_hash', 64)->index();
            $table->timestamps();
            $table->index(['lead_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
