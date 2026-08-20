<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('price_monthly')->default(0);
            $table->unsignedBigInteger('price_yearly')->default(0);
            $table->unsignedInteger('max_users')->default(2);
            $table->unsignedInteger('max_cajas')->default(1);
            $table->unsignedInteger('sifen_documents_monthly')->default(0);
            $table->json('features')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
