<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('payment_id')->primary();
            $table->foreignUuid('user_id')->constrained('users', 'uuid')->cascadeOnDelete();

            $table->string('order_id')->unique();
            $table->string('midtrans_transaction_id')->nullable();

            // pakai enum untuk status & tipe pembayaran
            $table->enum('payment_type', [
                'bank_transfer',
                'gopay',
                'qris',
                'credit_card',
                'shopeepay',
                'other'
            ])->nullable();

            $table->enum('transaction_status', [
                'pending',
                'settlement',
                'capture',
                'deny',
                'expire',
                'cancel',
                'refund',
            ])->default('pending');

            $table->enum('fraud_status', [
                'accept',
                'challenge',
                'deny'
            ])->nullable();

            $table->decimal('gross_amount', 15, 2);
            $table->json('payment_details')->nullable();

            $table->timestamp('transaction_time')->nullable();
            $table->timestamp('expiry_time')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
