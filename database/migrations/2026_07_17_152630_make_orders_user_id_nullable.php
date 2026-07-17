<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        // Гостевые заказы, ранее помеченные как user_id = 1,
        // становятся NULL, ТОЛЬКО если это реально не заказы юзера id=1.
        // Отличить их автоматически нельзя, поэтому старые записи трогать не будем.
        // Новые заказы после деплоя будут писаться правильно благодаря коду ниже.
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};