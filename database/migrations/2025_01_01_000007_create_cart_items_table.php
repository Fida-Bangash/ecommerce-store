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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('quantity')->default(1);

            // Snapshot of the unit price at the moment the item was added,
            // so historical cart totals stay stable even if the product
            // price changes later.
            $table->decimal('price', 10, 2);

            $table->timestamps();

            // A product can only appear once per cart; quantity is updated instead.
            $table->unique(['cart_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
