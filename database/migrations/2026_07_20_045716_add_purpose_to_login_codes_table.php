<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_codes', function (Blueprint $table): void {
            $table->string('purpose', 32)->default('login')->after('email');
            $table->index(['email', 'purpose', 'consumed_at', 'expires_at'], 'login_codes_email_purpose_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('login_codes', function (Blueprint $table): void {
            $table->dropIndex('login_codes_email_purpose_lookup');
            $table->dropColumn('purpose');
        });
    }
};