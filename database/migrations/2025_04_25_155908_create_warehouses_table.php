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
          Schema::create('warehouses', function (Blueprint $table) {
            // - banner
            // - name
            // - description
            // - price
            // - location
            // - tag [new, trending]
            // - status [active | inactive]
            // - capacity [string]
            $table->id();
            $table->foreignId('banner_id')->nullable()->constrained('assets')->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('capacity')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->decimal('price', 10, 2);
            $table->string('tag')->nullable();
            $table->string('location')->nullable();
            // estimated_delivery
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
        Schema::dropIfExists('warehouses');
    }
};
