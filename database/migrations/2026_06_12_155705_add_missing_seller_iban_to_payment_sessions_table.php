<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_sessions', function (Blueprint $table) {
            // Eksik olan seller_iban sütununu tabloya ekliyoruz
            $table->string('seller_iban')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payment_sessions', function (Blueprint $table) {
            // Geri alma durumu için sütunu silme kodu
            $table->dropColumn('seller_iban');
        });
    }
};
