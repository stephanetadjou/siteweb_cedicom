<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Back office : paramètres du site, diaporama du hero, marques masquables / mises en avant. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->json('alt');
            $table->string('position', 30)->default('50% 50%'); // cadrage CSS object-position
            $table->string('veil', 10)->default('light'); // light | strong
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('visible')->default(true);
            $table->timestamps();
        });

        Schema::table('partner_brands', function (Blueprint $table) {
            $table->boolean('visible')->default(true)->after('sort');
            $table->boolean('featured')->default(false)->after('visible');
        });
    }

    public function down(): void
    {
        Schema::table('partner_brands', function (Blueprint $table) {
            $table->dropColumn(['visible', 'featured']);
        });
        Schema::dropIfExists('hero_slides');
        Schema::dropIfExists('settings');
    }
};
