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
        Schema::table('holds', function (Blueprint $table) {
             $table->foreignId('used_for_order_id')->nullable()->constrained('orders')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('holds', function (Blueprint $table) {
            $table->dropForeign(['used_for_order_id']);
            $table->dropColumn('used_for_order_id');
        });
    }
};
