<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('team_usesrinv', function (Blueprint $table) {
            $table->id();
          $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->string('sent_at', 45)->nullable();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('receiver_id');

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_usesrinv');
    }
};

