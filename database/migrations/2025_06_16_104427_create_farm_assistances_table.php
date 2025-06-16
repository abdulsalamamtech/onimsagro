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
        Schema::create('farm_assistances', function (Blueprint $table) {
            // -   full_name
            // -   phone_number
            // -   email
            // -   assistance_types_id
            // -   reason_for_request
            $table->id();
            $table->string('full_name');
            $table->string('phone_number');
            $table->string('email');
            $table->foreignId('assistance_types_id')->nullable()->constrained('assistance_types')->nullOnDelete();
            $table->text('reason_for_request');
            // status
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
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
        Schema::dropIfExists('farm_assistances');
    }
};
