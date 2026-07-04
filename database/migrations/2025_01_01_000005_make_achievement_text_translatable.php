<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Make achievement `name` and `description` translatable: store a per-locale
 * JSON map ({"en": "...", "pl": "..."}) instead of a single string. Existing
 * plain-string values are wrapped under the app's fallback locale so no copy
 * is lost. Framework-native (no translation package required).
 */
return new class extends Migration
{
    public function up(): void
    {
        $fallback = (string) (config('app.fallback_locale') ?: 'en');

        // 1. Wrap any existing plain-string values into a {locale: value} map,
        //    stored as JSON text (columns are still string/text at this point).
        foreach (DB::table('achievements')->select('id', 'name', 'description')->get() as $row) {
            $update = [];

            if ($row->name !== null && ! $this->looksLikeJson($row->name)) {
                $update['name'] = json_encode([$fallback => $row->name]);
            }

            if ($row->description !== null && ! $this->looksLikeJson($row->description)) {
                $update['description'] = json_encode([$fallback => $row->description]);
            }

            if ($update !== []) {
                DB::table('achievements')->where('id', $row->id)->update($update);
            }
        }

        // 2. Promote the columns to JSON so per-locale searches (name->'pl') work.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE achievements ALTER COLUMN name TYPE json USING name::json');
            DB::statement('ALTER TABLE achievements ALTER COLUMN description TYPE json USING description::json');
        } else {
            Schema::table('achievements', function (Blueprint $table): void {
                $table->json('name')->change();
                $table->json('description')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        $fallback = (string) (config('app.fallback_locale') ?: 'en');

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE achievements ALTER COLUMN name TYPE varchar(255) USING (name::json->>?)', [$fallback]);
            DB::statement('ALTER TABLE achievements ALTER COLUMN description TYPE text USING (description::json->>?)', [$fallback]);

            return;
        }

        // Non-pgsql: collapse the JSON map back to the fallback string.
        foreach (DB::table('achievements')->select('id', 'name', 'description')->get() as $row) {
            DB::table('achievements')->where('id', $row->id)->update([
                'name' => $this->pluck($row->name, $fallback),
                'description' => $this->pluck($row->description, $fallback),
            ]);
        }

        Schema::table('achievements', function (Blueprint $table): void {
            $table->string('name')->change();
            $table->text('description')->nullable()->change();
        });
    }

    private function looksLikeJson(?string $value): bool
    {
        return $value !== null && Str::startsWith(trim($value), '{');
    }

    private function pluck(?string $json, string $locale): ?string
    {
        if ($json === null) {
            return null;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? ($decoded[$locale] ?? reset($decoded) ?: null) : $json;
    }
};
