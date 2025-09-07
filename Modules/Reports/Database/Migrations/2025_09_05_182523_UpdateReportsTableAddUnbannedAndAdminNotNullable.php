<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. تحديث enum لإضافة 'unbanned'
        DB::statement("ALTER TABLE reports MODIFY status ENUM('pending', 'notified', 'banned', 'unbanned') NOT NULL DEFAULT 'pending'");

        // 2. تعديل admin_id ليصبح NOT NULL مع FK cascade
        Schema::table('reports', function (Blueprint $table) {
            // حذف المفتاح الأجنبي القديم أولاً
            $table->dropForeign(['admin_id']);
        });

        // 3. تعديل العمود ليصبح NOT NULL
        DB::statement("ALTER TABLE reports MODIFY admin_id BIGINT UNSIGNED NOT NULL");

        // 4. إعادة إنشاء المفتاح الأجنبي مع onDelete('cascade')
        Schema::table('reports', function (Blueprint $table) {
            $table->foreign('admin_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        // 1. إعادة enum القديمة بدون 'unbanned'
        DB::statement("ALTER TABLE reports MODIFY status ENUM('pending', 'notified', 'banned') NOT NULL DEFAULT 'pending'");

        // 2. حذف المفتاح الأجنبي الحالي
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
        });

        // 3. إعادة العمود ليصبح nullable
        DB::statement("ALTER TABLE reports MODIFY admin_id BIGINT UNSIGNED NULL");

        // 4. إعادة المفتاح الأجنبي الأصلي مع onDelete('set null')
        Schema::table('reports', function (Blueprint $table) {
            $table->foreign('admin_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }
};
