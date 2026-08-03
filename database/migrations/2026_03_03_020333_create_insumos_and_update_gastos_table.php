<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('stock', 10, 2)->default(0);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });

        Schema::table('gastos', function (Blueprint $table) {
            $table->enum('type', ['gasto', 'insumo'])->default('gasto')->after('amount');
            $table->unsignedBigInteger('insumo_id')->nullable()->after('type');
            $table->decimal('quantity', 10, 2)->nullable()->after('insumo_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->dropColumn(['type', 'insumo_id', 'quantity']);
        });
        Schema::dropIfExists('insumos');
    }
};
