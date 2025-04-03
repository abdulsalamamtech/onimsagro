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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_type_id')->nullable()->constrained('product_types')->cascadeOnDelete();
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories')->cascadeOnDelete();
            $table->foreignId('banner_id')->nullable()->constrained('assets')->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->string('tag')->nullable();
            $table->string('location')->nullable();
            // estimated_delivery
            $table->integer('estimated_delivery')->default(2);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
