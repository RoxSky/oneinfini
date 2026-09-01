<?php

namespace App\Services\Payments;

use App\Models\Booking;

class MockPaymentService implements PaymentService
{
    public function createPayment(Booking $booking, float $amount, string $method = 'QRIS'): array
    {
        // Simulasi bikin transaksi di provider (belum sukses, status Pending)
        return [
            'trx_id' => uniqid('mocktrx_'),
            'status' => 'Pending',
            'provider' => 'mock',
            // bisa juga kembalikan 'redirect_url' kalau mau arahkan ke halaman mock.
        ];
    }
}
