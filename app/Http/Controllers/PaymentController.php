<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceMail;
use App\Models\Booking;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    public function mockCheckout(Request $request)
    {
        // Prioritas: jika ada pending_payment_ids -> mode countdown normal
        $paymentIds = session('pending_payment_ids', []);
        $invoiceBookingIds = session('invoice_booking_ids', []);

        if (! empty($paymentIds)) {
            $payments = Payment::with('booking.timeSlot')
                ->whereIn('id', $paymentIds)
                ->get();

            if ($payments->isEmpty()) {
                return redirect('/')->with('error', 'Transaksi tidak ditemukan.');
            }

            $firstExpiry = $payments
                ->map(fn ($p) => \Carbon\Carbon::parse($p->created_at)->addMinutes(10))
                ->sort()
                ->first();

            if ($firstExpiry->isPast()) {
                return $this->expirePendingPaymentsAndRedirect($paymentIds);
            }

            return view('payments.mock_checkout', [
                'payments' => $payments,
                'firstExpiry' => $firstExpiry,
                'invoiceBookings' => null,
                'invoicePaymentsMap' => null,
            ]);
        }

        // Jika tidak ada pending, namun ada invoice_booking_ids (flash after success), tampilkan modal invoice
        if (! empty($invoiceBookingIds)) {
            $invoiceBookings = Booking::with(['user', 'timeSlot'])
                ->whereIn('id', $invoiceBookingIds)
                ->get();

            $invoicePayments = Payment::whereIn('booking_id', $invoiceBookingIds)
                ->get()
                ->groupBy('booking_id');

            return view('payments.mock_checkout', [
                'payments' => collect(), // tidak ada tabel transaksi yang menunggu
                'firstExpiry' => null,
                'invoiceBookings' => $invoiceBookings,
                'invoicePaymentsMap' => $invoicePayments,
            ]);
        }

        return redirect('/')->with('error', 'Tidak ada transaksi yang menunggu konfirmasi.');
    }

    public function mockExpire(Request $request)
    {
        $paymentIds = session('pending_payment_ids', []);
        if (empty($paymentIds)) {
            return redirect('/')->with('expired', 'Booking Expired! Waktu pembayaran telah habis.');
        }

        return $this->expirePendingPaymentsAndRedirect($paymentIds);
    }

    private function expirePendingPaymentsAndRedirect(array $paymentIds)
    {
        DB::transaction(function () use ($paymentIds) {
            $payments = Payment::with('booking')
                ->whereIn('id', $paymentIds)
                ->lockForUpdate()
                ->get();

            foreach ($payments as $payment) {
                // Hanya set Failed & release booking kalau payment masih Pending.
                if (strtolower($payment->status) === 'pending') {
                    $payment->status = 'Failed';
                    $payment->save();

                    if ($payment->booking) {
                        $payment->booking->update([
                            'booking_status' => 'available',
                            'payment_status' => 'Pending',
                            'expires_at' => null,
                            'user_id' => null,
                            'admin_id' => null,
                        ]);
                    }
                }
                // Jika payment sudah Success, jangan release booking.
            }
        });

        // Bersihkan session mock
        session()->forget(['pending_payment_ids', 'selected_slots', 'total_price', 'invoice_booking_ids']);

        return redirect('/')->with(
            'expired',
            'Booking Expired! Waktu pembayaran telah habis. Silakan pilih jadwal lagi.'
        );
    }

    public function mockConfirm(Request $request)
    {
        $request->validate([
            'action' => 'required|in:success,fail',
        ]);

        $paymentIds = session('pending_payment_ids', []);
        if (empty($paymentIds)) {
            return redirect()->route('mock.checkout')->with('error', 'Session transaksi tidak ditemukan.');
        }

        $action = $request->input('action');
        $invoiceBookingIds = [];
        $trxToEmail = [];

        DB::transaction(function () use ($paymentIds, $action, &$invoiceBookingIds, &$trxToEmail) {
            $payments = Payment::with('booking.user', 'booking.timeSlot')
                ->whereIn('id', $paymentIds)
                ->lockForUpdate()
                ->get();

            foreach ($payments as $payment) {
                $payment->update([
                    'status' => $action === 'success' ? 'Success' : 'Failed',
                    'payment_date' => now(),
                ]);
            }

            $byBooking = $payments->groupBy('booking_id');

            foreach ($byBooking as $bookingId => $group) {
                $booking = $group->first()->booking;
                if (! $booking) {
                    continue;
                }

                if ($action === 'fail') {
                    $booking->update([
                        'payment_status' => 'Pending',
                        'booking_status' => 'available',
                        'user_id' => null,
                        'admin_id' => null,
                        'expires_at' => null,
                    ]);

                    continue;
                }

                $successPaid = Payment::where('booking_id', $bookingId)
                    ->where('status', 'Success')
                    ->sum('amount');

                $newPaymentStatus = 'Pending';
                if ($successPaid + 0.01 < (float) $booking->total_price && $successPaid > 0) {
                    $newPaymentStatus = 'DP';
                } elseif ($successPaid + 0.01 >= (float) $booking->total_price) {
                    $newPaymentStatus = 'Paid';
                }

                $booking->update([
                    'payment_status' => $newPaymentStatus,
                    'booking_status' => 'booked',
                    'expires_at' => null,
                ]);

                // Tambah point user jika ada
                if ($booking->user) {
                    $pricePerPoint = (int) DB::table('settings')->where('key', 'price_per_point')->value('value') ?? 100000;
                    $totalPoints = $pricePerPoint > 0 ? floor($successPaid / $pricePerPoint) : 0;
                    if ($totalPoints > 0) {
                        $booking->user->increment('points', $totalPoints);
                    }
                }

                $invoiceBookingIds[] = $booking->id;
            }

            // Ambil trx unik untuk dikirimkan email
            $trxToEmail = $payments->pluck('trx_id')->unique()->values();
        });

        /**
         * Kirim email HANYA SEKALI per trx_id
         */
        foreach ($trxToEmail as $trxId) {
            // Ambil ulang dari DB (data fresh)
            $group = Payment::with(['booking.user', 'booking.timeSlot'])
                ->where('trx_id', $trxId)
                ->where('status', 'Success')
                ->get();

            if ($group->isEmpty()) {
                continue;
            }

            $user = optional($group->first()->booking)->user;
            if ($user && $user->email) {
                $bookingsInTransaction = $group->pluck('booking')->filter()->unique('id');

                // Kirim hanya SEKALI per transaksi
                Mail::to($user->email)->send(new InvoiceMail($bookingsInTransaction, $trxId));
            }
        }

        // Hapus session setelah transaksi
        session()->forget(['pending_payment_ids', 'selected_slots', 'total_price']);

        if ($action === 'success') {
            session()->flash('invoice_booking_ids', $invoiceBookingIds);
            session()->flash('success', 'Pembayaran berhasil (simulasi).');

            return redirect()->route('booking.index');
        }

        return redirect()->route('mock.checkout')->with('error', 'Pembayaran gagal (simulasi).');
    }

    // Dipanggil saat user klik "Kembali ke Halaman Utama" dari modal invoice
    public function mockFinish(Request $request)
    {
        session()->forget(['pending_payment_ids', 'selected_slots', 'total_price', 'invoice_booking_ids']);

        return redirect()->route('booking.index')->with('success', 'Pembayaran & booking tercatat. Terima kasih!');
    }

    public function downloadInvoice($id)
    {
        $booking = Booking::with(['user', 'timeSlot'])->findOrFail($id);
        $payment = Payment::where('booking_id', $id)->where('status', 'Success')->first();

        $pdf = Pdf::loadView('invoices.invoice', compact('booking', 'payment'));

        return $pdf->download("invoice-{$booking->id}.pdf");
    }
}
