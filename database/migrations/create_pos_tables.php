<?php
// ============================================================
// Migration 1: pos_api_clients tablosu
// Çalıştır: php artisan migrate
// ============================================================
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Yetkili E-ticaret Siteleri ─────────────────────────────────────
        Schema::create('pos_api_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // Site adı
            $table->string('api_key')->unique();             // X-POS-API-KEY
            $table->string('api_secret');                    // HMAC imza anahtarı
            $table->string('webhook_secret');                // Webhook imza anahtarı
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Ödeme Oturumları ───────────────────────────────────────────────
        Schema::create('payment_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                  ->constrained('pos_api_clients')
                  ->onDelete('cascade');
            $table->string('token', 40)->unique();           // tok_xxx
            $table->string('order_id', 100);                 // E-ticaret sipariş ID
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('TRY');
            $table->string('description', 255);
            $table->string('customer_name', 100);
            $table->string('customer_email', 150);
            $table->string('return_url', 500);
            $table->string('webhook_url', 500);
            $table->enum('status', ['pending','completed','failed','expired'])
                  ->default('pending');
            $table->unsignedBigInteger('transaction_id')->nullable(); // FK sonradan eklenir
            $table->timestamp('expires_at');
            $table->timestamps();

            // Performans için index'ler
            $table->index(['client_id', 'order_id', 'status']);
            $table->index('expires_at');
        });

        // ── İşlem Kayıtları ────────────────────────────────────────────────
        Schema::create('pos_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')
                  ->constrained('payment_sessions')
                  ->onDelete('cascade');
            $table->string('card_last_four', 4);             // Tam kart No SAKLANMAZ
            $table->string('card_holder', 100);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('TRY');
            $table->enum('status', ['completed', 'failed']);
            $table->string('failure_reason')->nullable();
            $table->timestamp('processed_at');
            $table->timestamps();
        });

        // transaction_id FK'yı şimdi ekle (döngüsel bağımlılık nedeniyle)
        Schema::table('payment_sessions', function (Blueprint $table) {
            $table->foreign('transaction_id')
                  ->references('id')
                  ->on('transactions')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('payment_sessions', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
        });
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('payment_sessions');
        Schema::dropIfExists('pos_api_clients');
    }
};
