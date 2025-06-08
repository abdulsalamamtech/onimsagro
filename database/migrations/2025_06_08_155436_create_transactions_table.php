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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('warehouse_order_id')->nullable()->constrained('warehouse_orders')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('cascade');
            // payment_type [warehouse, product, consultation, donation, new]
            $table->string('payment_type')->default('product');
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('status', 
                [
                    'pending', 
                    'successful', 
                    'cancelled', 
                    'suspended', 
                    'rejected'
                ])
                ->default('pending');
            $table->string('reference')->unique();
            $table->string('payment_method')->default('online');
            $table->string('payment_provider')->default('paystack');
            $table->json('data')->nullable(); // response data from payment server
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
