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
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('ruc')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->string('admin_name')->nullable();
            $table->string('admin_email')->nullable();
            $table->string('admin_password_hash')->nullable();
            $table->string('brand_color')->nullable();
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamps();
            $table->json('data')->nullable();
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE tenants ADD CONSTRAINT tenants_slug_format CHECK (slug ~ '^[a-z0-9]+$')");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
