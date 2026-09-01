<?php

namespace App\Services\Payments;

use App\Models\Booking;

interface PaymentService
{
    /**
     * Inisialisasi transaksi ke provider (mock).
     * Mengembalikan array minimal: trx_id, status (Pending), provider (mock)
     */
    public function createPayment(Booking $booking, float $amount, string $method = 'QRIS'): array;
}
