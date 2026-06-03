<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'external_order_id')) {
                $table->string('external_order_id', 100)->nullable()->after('completed_at');
            }
            if (!Schema::hasColumn('orders', 'external_status')) {
                $table->string('external_status', 50)->nullable()->after('external_order_id');
            }
            
            $table->index('external_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['external_order_id']);
            $table->dropColumn(['external_order_id', 'external_status']);
        });
    }
};
