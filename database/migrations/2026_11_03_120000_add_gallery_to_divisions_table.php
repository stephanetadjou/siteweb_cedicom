<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('divisions', function (Blueprint $table) {
            // Photos secondaires affichées en vignettes : [{"image": ..., "alt": {"fr": ..., "en": ...}}]
            $table->json('gallery')->nullable()->after('image_alt');
        });
    }

    public function down(): void
    {
        Schema::table('divisions', function (Blueprint $table) {
            $table->dropColumn('gallery');
        });
    }
};
