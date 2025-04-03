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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('original_name')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            $table->string('path')->nullable();
            $table->string('file_id')->nullable();
            $table->string('url')->nullable();
            $table->string('transformation')->nullable();
            $table->integer('size')->nullable();
            $table->string('hosted_at')->nullable()->default('local'); // cloudinary | AWS S3
            $table->boolean('active')->default(true);
            $table->string('trashed')->nullable();
            $table->softDeletes();            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
