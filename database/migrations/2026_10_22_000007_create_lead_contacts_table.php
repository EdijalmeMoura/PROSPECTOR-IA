<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 20)->comment('email|whatsapp|instagram|telefone|site');
            $table->string('value');
            $table->string('permission_status', 20)->default('desconhecida');
            $table->timestamp('permission_verified_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['channel', 'permission_status']);
            $table->index('value');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_contacts');
    }
};
