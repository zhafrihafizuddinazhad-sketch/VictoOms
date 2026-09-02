<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
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
            'Out for Delivery',
            'Waiting for Pickup',
            'Completed'
        ) DEFAULT 'Pending'
    ");
}

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
            'Completed'
        ) DEFAULT 'Pending'
    ");
}
};