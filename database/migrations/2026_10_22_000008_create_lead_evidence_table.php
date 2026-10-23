<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_generation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 40)->comment('site_ausente, site_desatualizado, rede_social, portfolio, ...');
            $table->text('description');
            $table->string('source_url')->nullable();
            $table->string('reliability', 10)->default('media');
            $table->string('collected_by', 20)->default('manual')->comment('manual|provider|ai');
            $table->timestamp('observed_at')->nullable();
            $table->timestamps();
            $table->index(['lead_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_evidence');
    }
};
