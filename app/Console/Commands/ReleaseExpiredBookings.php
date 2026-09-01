<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReleaseExpiredBookings extends Command
{
    protected $signature = 'bookings:release-expired';

    protected $description = 'Release bookings whose payment window (expires_at) has passed while still Pending.';

    public function handle(): int
    {
        $now = now();
        $released = 0;

        Booking::where('booking_status', 'booked')
            ->where('payment_status', 'Pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->orderBy('id')
            ->chunkById(100, function ($bookings) use (&$released) {
                foreach ($bookings as $booking) {
                    DB::transaction(function () use ($booking, &$released) {
                        // Tandai semua payment Pending jadi Failed
                        Payment::where('booking_id', $booking->id)
                            ->where('status', 'Pending')
                            ->update([
                                'status' => 'Failed',
                                'payment_date' => now(),
                            ]);

                        // Lepas slot
                        $booking->update([
                            'payment_status' => 'Pending',
                            'booking_status' => 'available',
                            'user_id' => null,
                            'admin_id' => null,
                            'expires_at' => null,
                        ]);

                        $released++;
                    });
                }
            });

        $this->info("Released {$released} booking(s).");

        return 0;
    }
}
