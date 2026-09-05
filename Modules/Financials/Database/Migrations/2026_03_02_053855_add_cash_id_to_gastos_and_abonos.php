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
        Schema::table('gastos', function (Blueprint $table) {
            $table->unsignedBigInteger('cash_id')->nullable()->after('amount');
        });

        Schema::table('abonos', function (Blueprint $table) {
            $table->unsignedBigInteger('cash_id')->nullable()->after('amount');
        });
    }

    public function down()
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->dropColumn('cash_id');
        });

        Schema::table('abonos', function (Blueprint $table) {
            $table->dropColumn('cash_id');
        });
    }
};
