<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stadium_slots', function (Blueprint $table) {
            $table->id();
           $table->time('start_time');
            $table->time('end_time');
            $table->unsignedBigInteger('stadium_id');
             $table->enum('status', ['available', 'booked', 'maintenance'])->default('available');
            $table->timestamps();
            $table->foreign('stadium_id')->references('id')->on('stadiums')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stadium_slots');
    }
};

