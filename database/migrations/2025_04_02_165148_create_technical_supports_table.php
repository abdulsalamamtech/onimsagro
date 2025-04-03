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
        Schema::create('technical_supports', function (Blueprint $table) {
            // full_name
            // phone_number
            // email
            // crop_type_id
            // stage_of_plant
            // problem_with_crop
            $table->id();
            $table->foreignId('crop_type_id')->nullable()->constrained('crop_types')->nullOnDelete();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone_number');
            $table->string('problem_with_crop');
            $table->string('stage_of_plant');
            $table->enum('status', ['pending','solved', 'not_solved'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technical_supports');
    }
};
