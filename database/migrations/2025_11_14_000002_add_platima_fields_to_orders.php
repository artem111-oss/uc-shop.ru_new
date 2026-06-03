<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'game_id')) {
                $table->string('game_id', 20)->after('uid');
            }
            if (!Schema::hasColumn('orders', 'email')) {
                $table->string('email')->nullable()->after('game_id');
            }
            if (!Schema::hasColumn('orders', 'amount')) {
                $table->decimal('amount', 10, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('orders', 'uc_amount')) {
                $table->string('uc_amount', 50)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending')->after('status_id');
            }
            if (!Schema::hasColumn('orders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('updated_at');
            }
            
            $table->index(['game_id', 'payment_status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['game_id', 'email', 'amount', 'uc_amount', 'payment_status', 'completed_at']);
        });
    }
};
