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
        Schema::create('installation_services', function (Blueprint $table) {
            // -   full_name
            // -   phone_number
            // -   email
            // -   farm_size
            // -   installation_type_id
            // -   form_location
            // -   notes
            $table->id();
            $table->string('full_name');
            $table->string('phone_number');
            $table->string('email');
            $table->string('farm_size');
            $table->foreignId('installation_type_id')->nullable()->constrained('installation_types')->nullOnDelete();
            $table->string('farm_location');
            $table->text('notes')->nullable();
            // status
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installation_services');
    }
};
