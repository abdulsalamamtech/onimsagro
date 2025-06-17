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
        Schema::create('rental_services', function (Blueprint $table) {
        // -   full_name
        // -   phone_number
        // -   email
        // -   farm_size
        // -   equipment_type_id
        // -   address
        // -   state
        // -   renting_purpose
        // -   duration [7, 14 days]
        // -   duration_unit ['days', 'weeks', 'months', 'years']
        // -   amount
        // -   notes
        // -   status          
        // -   created_by
        // -   updated_by

            $table->id();
            $table->string('full_name');
            $table->string('phone_number');
            $table->string('email');
            $table->string('farm_size');
            $table->string('equipment_type_id');
            $table->string('address');
            $table->string('state');
            // renting_purpose
            $table->text('renting_purpose')->default('');
            $table->string('duration');
            // duration unit enum of days, weeks, months, years
            $table->enum('duration_unit', ['days', 'weeks', 'months', 'years'])->default('days');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('notes')->default(''); // by admin
            // $table->string('status');
            $table->enum('status', ['pending', 'approved', 'rejected', 'returned'])->default('pending');
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
        Schema::dropIfExists('rental_services');
    }
};
