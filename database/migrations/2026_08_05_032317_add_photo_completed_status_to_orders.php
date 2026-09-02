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
    DB::statement("
        ALTER TABLE orders
        MODIFY status ENUM(
            'Pending',
            'Assigned',
            'In Progress',
            'Pending Approval',
            'Printing',
            'Ready at HQ',
            'Photo Session',
            'Photo Completed',
            'Out for Delivery',
            'Waiting for Pickup',
            'Completed'
        ) DEFAULT 'Pending'
    ");
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    DB::statement("
        ALTER TABLE orders
        MODIFY status ENUM(
            'Pending',
            'Assigned',
            'In Progress',
            'Pending Approval',
            'Printing',
            'Ready at HQ',
            'Photo Session',
            'Out for Delivery',
            'Waiting for Pickup',
            'Completed'
        ) DEFAULT 'Pending'
    ");
}
};
