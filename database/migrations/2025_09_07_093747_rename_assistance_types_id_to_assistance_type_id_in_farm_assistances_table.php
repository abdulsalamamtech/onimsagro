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
        Schema::table('farm_assistances', function (Blueprint $table) {
            // php artisan make:migration rename_assistance_types_id_to_assistance_type_id_in_farm_assistances_table
            $table->renameColumn('assistance_types_id', 'assistance_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farm_assistances', function (Blueprint $table) {
            $table->renameColumn('assistance_type_id', 'assistance_types_id');
        });
    }
};
