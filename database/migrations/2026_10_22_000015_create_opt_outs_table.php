<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opt_outs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel', 20);
            $table->string('identifier')->comment('E-mail, telefone ou handle normalizado');
            $table->string('source', 30)->default('manual')->comment('manual|webhook|resposta|titular');
            $table->text('reason')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamps();
            // Uma única entrada por canal+identificador.
            $table->unique(['channel', 'identifier']);
            $table->index('requested_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opt_outs');
    }
};
