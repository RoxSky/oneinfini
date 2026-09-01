<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->enum('method', ['QRIS', 'Transfer']);
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['Pending', 'Success', 'Failed'])->default('Pending');
            $table->string('trx_id')->nullable(); // transaction ID dari payment gateway
            $table->timestamp('payment_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
