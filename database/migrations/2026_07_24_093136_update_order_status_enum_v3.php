<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tukar data lama dulu
        DB::statement("
            UPDATE orders
            SET status = 'Designing'
            WHERE status = 'In Progress'
        ");

        DB::statement("
            UPDATE orders
            SET status = 'Owner Review'
            WHERE status = 'Pending Approval'
        ");

        // Baru tukar ENUM
        DB::statement("
    ALTER TABLE orders
    MODIFY COLUMN status ENUM(
        'Pending',
        'Assigned',
        'In Progress',
        'Pending Approval',
        'Designing',
        'Owner Review',
        'Printing',
        'Completed'
    ) NOT NULL DEFAULT 'Pending'
");
    }

    public function down(): void
    {
        // Tukar balik data
        DB::statement("
            UPDATE orders
            SET status = 'In Progress'
            WHERE status = 'Designing'
        ");

        DB::statement("
            UPDATE orders
            SET status = 'Pending Approval'
            WHERE status = 'Owner Review'
        ");

        // Kembalikan ENUM asal
        DB::statement("
            ALTER TABLE orders
            MODIFY COLUMN status ENUM(
                'Pending',
                'Assigned',
                'In Progress',
                'Pending Approval',
                'Printing',
                'Completed'
            ) NOT NULL DEFAULT 'Pending'
        ");
    }
};