<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'product_kind')) {
                $table->string('product_kind')->default('uc')->after('delivery_mode');
            }
            if (!Schema::hasColumn('products', 'api_title')) {
                $table->string('api_title')->nullable()->after('product_kind');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['product_kind', 'api_title']);
        });
    }
};