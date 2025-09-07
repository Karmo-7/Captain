<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('team_usesrinv', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropForeign(['receiver_id']);
        });
    }

    public function down(): void
    {
        Schema::table('team_usesrinv', function (Blueprint $table) {
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
