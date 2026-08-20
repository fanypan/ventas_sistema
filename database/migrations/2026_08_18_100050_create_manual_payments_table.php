<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_payments', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->foreignId('platform_user_id')->nullable()->constrained('platform_users')->nullOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('method');
            $table->string('reference')->nullable();
            $table->timestamp('paid_at');
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_payments');
    }
};
