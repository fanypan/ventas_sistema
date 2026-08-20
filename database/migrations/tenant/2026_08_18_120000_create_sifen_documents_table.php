<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sifen_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->string('document_type')->default('factura');
            $table->string('cdc')->nullable();
            $table->string('status')->default('pending');
            $table->string('partner_reference')->nullable();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sifen_documents');
    }
};
