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
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stadium_id')->constrained('stadiums')->onDelete('cascade');
            $table->enum('name', ['Toilets','Reception','Buffet','Cafeteria','Sports Equipment','Locker Rooms','First Aid Room','Parking','Wi-Fi','Spectator Seats','Display Screen', 'Sound System','Night Lighting']);
            $table->integer('quantity')->default(1);
            $table->text('description')->nullable();
            $table->json('photos')->nullable();
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
        Schema::dropIfExists('facilities');
    }
};
