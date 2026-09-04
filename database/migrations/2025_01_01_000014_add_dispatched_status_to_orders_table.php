<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds "dispatched" to the orders.status enum, sitting between
     * "processing" and "completed" in the order lifecycle:
     * pending -> processing -> dispatched -> completed
     * (with "cancelled" possible up until "completed", and refunds
     * tracked separately via the refunded_at column).
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE orders MODIFY COLUMN status ENUM('pending','processing','dispatched','completed','cancelled') NOT NULL DEFAULT 'pending'"
            );
        }

        // Note: this migration targets MySQL (the production driver for
        // this app). If you ever run this project on SQLite locally,
        // the status column there is a loose CHECK constraint and will
        // need to be recreated manually to accept 'dispatched'.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                "UPDATE orders SET status = 'processing' WHERE status = 'dispatched'"
            );

            DB::statement(
                "ALTER TABLE orders MODIFY COLUMN status ENUM('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending'"
            );
        }
    }
};
