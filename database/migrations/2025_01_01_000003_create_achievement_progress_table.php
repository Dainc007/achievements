<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievement_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('achievement_id')->constrained()->cascadeOnDelete();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedInteger('current')->default(0);
            $table->unsignedInteger('target');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['achievement_id', 'subject_type', 'subject_id'], 'achievement_progress_unique_subject');
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_progress');
    }
};
