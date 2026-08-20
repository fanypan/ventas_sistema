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
        Schema::table('abonos', function (Blueprint $table) {
            $table->string('reference')->nullable()->after('payment_method');
            $table->text('note')->nullable()->after('reference');
            $table->integer('installment_number')->nullable()->after('note');
            $table->decimal('received_amount', 15, 2)->nullable()->after('installment_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('abonos', function (Blueprint $table) {
            $table->dropColumn(['reference', 'note', 'installment_number', 'received_amount']);
        });
    }
};
