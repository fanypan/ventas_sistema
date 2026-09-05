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
        Schema::table('tenants', function (Blueprint $table) {
            $table->timestamp('admin_password_set_at')->nullable();
        });

        DB::table('tenants')
            ->whereNotNull('provisioned_at')
            ->whereNull('admin_password_set_at')
            ->update(['admin_password_set_at' => DB::raw('provisioned_at')]);
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('admin_password_set_at');
        });
    }
};
