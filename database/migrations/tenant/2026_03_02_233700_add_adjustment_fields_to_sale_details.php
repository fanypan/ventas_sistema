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
        Schema::table('sale_details', function (Blueprint $table) {
            $table->decimal('discount', 12, 2)->default(0)->after('price');
            $table->decimal('interest_amount', 12, 2)->default(0)->after('discount');
        });

        Schema::table('temporary_details', function (Blueprint $table) {
            $table->decimal('discount', 12, 2)->default(0)->after('price');
            $table->decimal('interest_amount', 12, 2)->default(0)->after('discount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropColumn(['discount', 'interest_amount']);
        });

        Schema::table('temporary_details', function (Blueprint $table) {
            $table->dropColumn(['discount', 'interest_amount']);
        });
    }
};
