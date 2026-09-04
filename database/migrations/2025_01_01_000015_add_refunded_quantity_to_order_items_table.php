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
        Schema::table('order_items', function (Blueprint $table) {
            // How many units of this line item have been refunded so far.
            // Lets an admin refund a single product out of a multi-item
            // order instead of only being able to refund the whole order.
            $table->unsignedInteger('refunded_quantity')->default(0)->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('refunded_quantity');
        });
    }
};
