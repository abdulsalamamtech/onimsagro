<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow 'failed' as a transaction status, used when Paystack reports a
     * payment whose amount or currency does not match what we expected.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('status', ['pending', 'successful', 'failed', 'cancelled', 'suspended', 'rejected'])
                ->default('pending')
                ->change();
        });
    }

    public function down(): void
    {
        DB::table('transactions')->where('status', 'failed')->update(['status' => 'rejected']);

        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('status', ['pending', 'successful', 'cancelled', 'suspended', 'rejected'])
                ->default('pending')
                ->change();
        });
    }
};
