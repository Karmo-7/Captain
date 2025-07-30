<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvitationMatchesTable extends Migration
{
    public function up()
    {
        Schema::create('invitation_matches', function (Blueprint $table) {
            $table->id();

            $table->string('proposed_date', 45)->nullable();
            $table->string('status', 45)->nullable();
            $table->string('sent_at', 45)->nullable();

            $table->unsignedBigInteger('sender_team_id')->nullable();;
            $table->unsignedBigInteger('receiver_team_id')->nullable();;
            $table->unsignedBigInteger('stadium_id');
            $table->unsignedBigInteger('slot_id');
           $table->unsignedBigInteger('league_id')->nullable();


            $table->timestamps();

            //  Foreign Keys
            $table->foreign('sender_team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('receiver_team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('stadium_id')->references('id')->on('stadiums')->onDelete('cascade');
            $table->foreign('slot_id')->references('id')->on('stadium_slots')->onDelete('cascade');
            $table->foreign('league_id')->references('id')->on('leagues')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invitation_matches');
    }
}
