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
        Schema::create('farmers', function (Blueprint $table) {
            // -   full_name
            // -   phone_number
            // -   email
            // -   country
            // -   state
            // -   address
            // -   farm_name
            // -   farm_size
            // -   type_of_farming_id
            // -   main_products
            // -   do_you_own_farming_equipment [yes|no]
            // -   where_do_you_sell_your_products
            // -   challenge_in_selling_your_products
            // -   additional_comment
            $table->id();
            $table->string('full_name');
            $table->string('phone_number');
            $table->string('email')->unique()->nullable();
            $table->string('address')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable()->default('Nigeria'); // Default to Nigeria, can be changed later
            $table->string('farm_name')->nullable();
            $table->decimal('farm_size', 10, 2)->nullable(); // Assuming farm size is in acres or hectares
            $table->string('farm_size_unit')->default('acres');
            $table->foreignId('type_of_farming_id')->nullable()->constrained('type_of_farmings')->nullOnDelete();
            $table->string('main_products')->nullable(); // Comma-separated list of main products
            $table->enum('do_you_own_farming_equipment', ['yes', 'no'])->default('no');
            $table->text('where_do_you_sell_your_products')->nullable();
            $table->text('challenge_in_selling_your_products')->nullable();
            $table->text('additional_comment')->nullable();
            // status
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes(); // For soft delete functionality
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farmers');
    }
};
