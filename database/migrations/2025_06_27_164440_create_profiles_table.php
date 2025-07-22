<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birthdate');
            $table->string('address');
            $table->bigInteger('phone_number')->unique();
            $table->string('avatar')->nullable();
            $table->enum('gender', ['male', 'female']);
            $table->integer('height'); //cm
            $table->integer('weight'); // بالـ kg
            $table->string('Sport');
            $table->string('positions_played');
            $table->text('notable_achievements')->nullable();
            $table->integer('years_of_experience');
            $table->text('previous_teams')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
