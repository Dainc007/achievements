<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievement_awards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('achievement_id')->constrained()->cascadeOnDelete();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->timestamp('awarded_at');
            $table->timestamp('revoked_at')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
        });

        // Partial unique index: at most one *active* (non-revoked) award per
        // (achievement, subject), while allowing revoked rows to remain as history
        // so the achievement can be re-earned. Postgres + SQLite support this.
        DB::statement(
            'CREATE UNIQUE INDEX achievement_awards_unique_active ON achievement_awards '.
            '(achievement_id, subject_type, subject_id) WHERE revoked_at IS NULL'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_awards');
    }
};
