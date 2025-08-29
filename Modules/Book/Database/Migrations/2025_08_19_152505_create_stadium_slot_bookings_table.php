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
        Schema::create('stadium_slot_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stadium_slot_id')->constrained('stadium_slots')->onDelete('cascade');
            $table->foreignId('stadium_id')->constrained('stadiums')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->enum('payment_type', ['full', 'deposit']);
            $table->decimal('amount_paid', 8, 2)->default(0);//المبلغ المدفوع حتى الان من قيمة هذا الحجز
            $table->enum('status', ['booked', 'cancelled'])->default('booked');
            $table->enum('payment_status', ['pending', 'partial', 'completed'])->default('pending');//عملية الدفع هل هي مكتملة او تم دفع جزء او انتظار
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
        Schema::dropIfExists('stadium_slot_bookings');
    }
};
