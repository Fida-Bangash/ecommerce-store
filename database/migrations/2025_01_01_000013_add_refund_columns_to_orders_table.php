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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('refunded_at')->nullable()->after('status');
            $table->foreignId('refunded_by')->nullable()->after('refunded_at')->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->after('refunded_by')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('refunded_by');
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn('refunded_at');
        });
    }
};
