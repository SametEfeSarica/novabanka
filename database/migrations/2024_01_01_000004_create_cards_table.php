<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->string('card_number', 16)->unique();
            $table->string('card_holder_name');
            $table->string('expiry_month', 2);
            $table->string('expiry_year', 4);
            $table->string('cvv', 3);
            $table->string('card_type')->default('virtual');
            $table->string('card_brand')->default('visa');
            $table->decimal('spending_limit', 15, 2)->nullable();
            $table->decimal('spent_today', 15, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_frozen')->default(false);
            $table->boolean('online_shopping')->default(true);
            $table->boolean('contactless')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};