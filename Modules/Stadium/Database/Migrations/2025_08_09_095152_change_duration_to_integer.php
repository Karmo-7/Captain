<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stadiums', function (Blueprint $table) {
            $table->integer('duration')->change();
        });

        Schema::table('stadium_requests', function (Blueprint $table) {
            $table->integer('duration')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stadiums', function (Blueprint $table) {
            $table->string('duration')->change();
        });

        Schema::table('stadium_requests', function (Blueprint $table) {
            $table->string('duration')->change();
        });
    }
};
