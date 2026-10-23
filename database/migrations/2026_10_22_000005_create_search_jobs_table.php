<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 40)->default('null')->comment('Fonte de pesquisa');
            $table->string('status', 20)->default('queued')->index();
            $table->string('segment')->nullable();
            $table->json('cities')->comment('Municípios selecionados');
            $table->string('state', 2)->default('PE');
            $table->unsignedSmallInteger('target_quantity')->default(50);
            $table->unsignedSmallInteger('found_count')->default(0);
            $table->unsignedSmallInteger('duplicate_count')->default(0);
            $table->unsignedSmallInteger('rejected_count')->default(0);
            $table->unsignedSmallInteger('qualified_count')->default(0);
            $table->unsignedTinyInteger('progress')->default(0)->comment('0-100');
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_jobs');
    }
};
