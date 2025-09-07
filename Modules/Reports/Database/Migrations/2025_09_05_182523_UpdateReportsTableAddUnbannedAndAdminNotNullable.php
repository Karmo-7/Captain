<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // 1. تحديث enum لإضافة 'unbanned'
        DB::statement("ALTER TABLE reports MODIFY status ENUM('pending', 'notified', 'banned', 'unbanned') NOT NULL DEFAULT 'pending'");

        // 2. إزالة المفتاح الأجنبي القديم
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
        });

        // 3. جعل admin_id NOT NULL
        DB::statement("ALTER TABLE reports MODIFY admin_id BIGINT UNSIGNED NOT NULL");

        // 4. إعادة ربط المفتاح الخارجي مع cascade
        Schema::table('reports', function (Blueprint $table) {
            $table->foreign('admin_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        // 1. إرجاع enum للحالات القديمة
        DB::statement("ALTER TABLE reports MODIFY status ENUM('pending', 'notified', 'banned') NOT NULL DEFAULT 'pending'");

        // 2. حذف المفتاح الأجنبي الحالي
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
        });

        // 3. جعل العمود nullable مرة ثانية
        DB::statement("ALTER TABLE reports MODIFY admin_id BIGINT UNSIGNED NULL");

        // 4. إعادة المفتاح الأجنبي مع set null (الوضع الأصلي)
        Schema::table('reports', function (Blueprint $table) {
            $table->foreign('admin_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }
};
