<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_sessions', function (Blueprint $table) {
            // Satıcının IBAN'ı — ödeme tamamlanınca bu hesaba transfer yapılır
            $table->string('seller_iban', 26)->nullable()->after('webhook_url');
        });
    }

    public function down(): void
    {
        Schema::table('payment_sessions', function (Blueprint $table) {
            $table->dropColumn('seller_iban');
        });
    }
};
