<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $bookings;

    public $trxId;

    public function __construct(Collection $bookings, string $trxId)
    {
        $this->bookings = $bookings->unique('id');
        $this->trxId = $trxId;
    }

    public function build()
    {
        $firstBooking = $this->bookings->first();

        // total harga semua booking
        $totalBookingPrice = $this->bookings->sum('total_price');

        // 1) coba ambil payments berdasarkan trx_id (ideal)
        $payments = collect();
        if ($this->trxId) {
            $payments = Payment::where('trx_id', $this->trxId)
                ->where('status', 'Success')
                ->get();
        }

        // 2) fallback: jika trx_id tidak menghasilkan apa-apa, ambil berdasarkan booking_id
        if ($payments->isEmpty()) {
            $payments = Payment::whereIn('booking_id', $this->bookings->pluck('id')->toArray())
                ->where('status', 'Success')
                ->get();
        }

        // total yang benar-benar dibayar
        $totalPaid = (float) $payments->sum('amount');

        // metode pembayaran yang digunakan (gabungkan jika lebih dari 1)
        $methods = $payments->pluck('method')->filter()->unique()->values()->all();
        $paymentMethods = empty($methods) ? null : implode(', ', $methods);

        // payment representative (untuk tanggal / fallback)
        $firstPayment = $payments->sortByDesc('id')->first() ?? ($firstBooking->payment ?? null);

        // overall payment status berdasarkan totalPaid vs totalBookingPrice
        if ($totalPaid >= $totalBookingPrice && $totalPaid > 0) {
            $overallStatus = 'Paid';
        } elseif ($totalPaid > 0) {
            $overallStatus = 'DP';
        } else {
            $overallStatus = $firstPayment && strtolower($firstPayment->status) === 'success' ? 'Paid' : 'Pending';
        }

        // jika tetap tidak ada payment object, buat dummy agar view tidak error
        if (! $firstPayment) {
            $firstPayment = (object) [
                'method' => $paymentMethods ?? '-',
                'amount' => $totalPaid,
                'status' => $overallStatus,
                'payment_date' => null,
            ];
        }

        return $this->subject('Bukti Pembayaran Booking Lapangan (Invoice #'.($this->trxId ?? '-').')')
            ->view('emails.invoice')
            ->with([
                'bookings' => $this->bookings,
                'booking' => $firstBooking,
                'trxId' => $this->trxId,
                'total_price' => $totalBookingPrice,
                'paid_amount' => $totalPaid,
                'payment_methods' => $paymentMethods,
                'overall_payment_status' => $overallStatus,
                'payment' => $firstPayment,
            ]);
    }
}
