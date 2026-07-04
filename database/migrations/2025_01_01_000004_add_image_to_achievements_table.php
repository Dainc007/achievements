<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('achievements', function (Blueprint $table): void {
            // Path to a custom badge image on the configured disk
            // (config: achievements.image_disk). Takes precedence over `icon`.
            $table->string('image')->nullable()->after('icon');
        });
    }

    public function down(): void
    {
        Schema::table('achievements', function (Blueprint $table): void {
            $table->dropColumn('image');
        });
    }
};
