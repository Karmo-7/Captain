<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::table('leagues', function (Blueprint $table) {
            if (!Schema::hasColumn('leagues', 'stadium_id')) {
                $table->foreignId('stadium_id')
                      ->after('created_by')
                      ->constrained('stadiums')
                      ->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('leagues', function (Blueprint $table) {
            if (Schema::hasColumn('leagues', 'stadium_id')) {
                $table->dropForeign(['stadium_id']);
                $table->dropColumn('stadium_id');
            }
        });
    }
};
