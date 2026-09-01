<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->time('start_time'); // Jam mulai diskon berlaku
            $table->time('end_time');   // Jam selesai diskon berlaku

            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->unsignedDecimal('amount', 8, 2);
            $table->string('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('day_type', ['weekday', 'weekend', 'all'])->default('all');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
