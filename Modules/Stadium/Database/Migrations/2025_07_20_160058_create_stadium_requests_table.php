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
        Schema::create('stadium_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sport_id')->constrained('sports')->onDelete('cascade');
            $table->string('name');
            $table->string('location');
            $table->text('description');
            $table->json('photos')->nullable();
            $table->decimal('Length');
            $table->decimal('Width');
            $table->bigInteger('owner_number');
            $table->string('start_time', 45);
            $table->string('end_time', 45);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
           $table->decimal('price', 8, 2);
            $table->decimal('deposit', 8, 2);
            $table->integer('duration');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stadium_requests');
    }
};
