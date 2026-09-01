<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')
                ->nullable()
                ->constrained('admins')
                ->onDelete('set null');
            $table->foreignId('time_slot_id')->constrained()->onDelete('cascade');
            $table->date('date'); // Tanggal booking

            // Booking dan payment status
            $table->enum('booking_status', ['available', 'booked'])->default('available');
            $table->enum('payment_status', ['Pending', 'DP', 'Paid'])->default('Pending');

            $table->decimal('total_price', 10, 2);

            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
