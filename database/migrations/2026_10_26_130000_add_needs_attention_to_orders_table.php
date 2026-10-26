<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets the system flag an order for an admin to look at, e.g. when it was
     * paid for but there was not enough stock left to fulfil it.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('needs_attention')->default(false)->after('is_paid');
            $table->text('attention_note')->nullable()->after('needs_attention');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['needs_attention', 'attention_note']);
        });
    }
};
