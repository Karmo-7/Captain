<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // إضافة حالة warning في enum
        DB::statement("ALTER TABLE reports MODIFY status ENUM('pending', 'notified', 'banned', 'unbanned', 'warning') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        // إزالة حالة warning عند التراجع
        DB::statement("ALTER TABLE reports MODIFY status ENUM('pending', 'notified', 'banned', 'unbanned') NOT NULL DEFAULT 'pending'");
    }
};

