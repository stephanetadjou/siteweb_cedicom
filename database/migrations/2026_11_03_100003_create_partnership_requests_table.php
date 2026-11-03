<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partnership_requests', function (Blueprint $table) {
            $table->id();
            $table->string('company');
            $table->string('country');
            $table->string('contact_name')->nullable();
            $table->string('email');
            $table->string('phone', 40)->nullable();
            $table->string('sector');
            $table->text('message')->nullable();
            $table->string('locale', 2)->default('fr');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partnership_requests');
    }
};
