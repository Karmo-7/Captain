<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('facility_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained('facilities')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('rating')->comment('1 to 5 stars');
            $table->text('review')->nullable();
            $table->timestamps();
            $table->unique(['facility_id','user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('facility_ratings');
    }
};
