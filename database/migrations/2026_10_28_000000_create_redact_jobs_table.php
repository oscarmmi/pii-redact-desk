<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redact_jobs', function (Blueprint $table): void {
            $table->id();
            $table->longText('source_text');
            $table->string('status')->default('completed');
            $table->timestamps();
        });

        Schema::create('redact_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('redact_job_id')->constrained()->cascadeOnDelete();
            $table->longText('redacted_text');
            $table->json('entities');
            $table->text('summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redact_results');
        Schema::dropIfExists('redact_jobs');
    }
};
