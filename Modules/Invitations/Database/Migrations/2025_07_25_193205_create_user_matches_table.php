<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('match_invitation_id');
            $table->timestamps();

            // علاقات
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('match_invitation_id')->references('id')->on('invitation_matches')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_matches');
    }
};
