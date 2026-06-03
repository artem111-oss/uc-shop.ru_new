<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promocode', function (Blueprint $table) {
          $table->id();
          $table->string('text');
          $table->unsignedBigInteger('value');
          $table->integer('product_id'); // у продукта есть остаток в промокодах - их продаем
          $table->integer('status'); // активность
          $table->timestamps();
          $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promocodes');
    }
};
