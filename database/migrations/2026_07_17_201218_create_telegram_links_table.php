<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('bot', 32);
            $table->unsignedBigInteger('telegram_id');
            $table->string('telegram_username', 255)->nullable();
            $table->string('telegram_first_name', 255)->nullable();
            $table->timestamp('linked_at')->nullable();
            $table->timestamps();

            $table->unique(['bot', 'telegram_id']);
            $table->index(['user_id', 'bot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_links');
    }
};