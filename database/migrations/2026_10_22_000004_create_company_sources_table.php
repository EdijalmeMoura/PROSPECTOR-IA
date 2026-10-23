<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('source_name', 80)->comment('google_places, manual, csv, ...');
            $table->string('source_url')->nullable();
            // Identificador externo confiável (ex.: place_id).
            $table->string('external_id')->nullable();
            $table->string('retention_condition', 20)->default('permanente');
            $table->json('metadata')->nullable()->comment('Metadados temporários sem segredos');
            $table->timestamp('collected_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('retention_expires_at')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            // Deduplicação por origem + identificador confiável.
            $table->unique(['source_name', 'external_id']);
            $table->index(['company_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_sources');
    }
};
