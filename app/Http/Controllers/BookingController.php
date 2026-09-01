<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Discount;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        // 1. Tentukan tanggal acuan dan rentang minggu
        // Ambil tanggal dari query parameter 'date' atau gunakan tanggal hari ini
        $currentDate = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : now();

        // Tentukan awal dan akhir minggu berdasarkan tanggal acuan
        $startOfWeek = $currentDate->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $startOfWeek->copy()->addDays(6);

        // 2. Ambil semua data yang dibutuhkan
        // Ambil semua slot waktu, diurutkan berdasarkan waktu mulai
        $timeSlots = TimeSlot::orderBy('start_time')->get();

        // Ambil semua booking dalam rentang minggu ini dengan scope query
        // Eager load relasi timeSlot dan user untuk efisiensi
        $bookings = Booking::whereBetween('date', [$startOfWeek, $endOfWeek])
            ->with(['timeSlot', 'user'])
            ->get()
            ->keyBy(function ($booking) {
                // Gunakan keyBy untuk mengubah koleksi menjadi map dengan key 'tanggal-slotId'
                return $booking->date.'-'.$booking->time_slot_id;
            });

        // 3. Siapkan data untuk tampilan
        // Buat koleksi tanggal untuk satu minggu penuh
        $dates = collect();
        for ($date = $startOfWeek->copy(); $date->lte($endOfWeek); $date->addDay()) {
            $dates->push($date->copy());
        }

        // 4. Kirim data ke view
        return view('booking.index', compact('dates', 'timeSlots', 'bookings', 'startOfWeek'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'slots' => 'required|array|min:1',
            'slots.*' => 'required|string', // Format: YYYY-MM-DD|slot_id
        ]);

        $selectedSlots = [];
        $totalPrice = 0;

        // Group booking by date
        $groupedByDate = collect($request->slots)->groupBy(function ($slot) {
            return explode('|', $slot)[0];
        });

        foreach ($groupedByDate as $date => $slotsInDay) {
            if (! Carbon::hasFormat($date, 'Y-m-d')) {
                continue;
            }

            $slotsData = [];
            $isWeekend = in_array(Carbon::parse($date)->dayOfWeekIso, [6, 7]);

            // Ambil semua slot TimeSlot yang valid
            foreach ($slotsInDay as $slot) {
                [, $slotId] = explode('|', $slot);
                $timeSlot = TimeSlot::find($slotId);
                if (! $timeSlot) {
                    continue;
                }

                // Cek apakah sudah dibooking
                $alreadyBooked = Booking::where('date', $date)
                    ->where('time_slot_id', $slotId)
                    ->where('booking_status', 'booked')
                    ->exists();

                if ($alreadyBooked) {
                    continue;
                }

                // Ambil booking jika ada (termasuk Available dgn harga custom)
                $existingBooking = Booking::where('date', $date)
                    ->where('time_slot_id', $slotId)
                    ->first();

                // Pakai harga custom jika ada, fallback ke default
                $basePrice = $existingBooking && $existingBooking->total_price
                    ? $existingBooking->total_price
                    : ($isWeekend
                        ? ($timeSlot->weekend_price ?? $timeSlot->price)
                        : ($timeSlot->weekday_price ?? $timeSlot->price)
                    );

                $slotsData[] = [
                    'slotId' => $slotId,
                    'timeSlot' => $timeSlot,
                    'basePrice' => $basePrice,
                ];
            }

            if (empty($slotsData)) {
                continue;
            }

            // Sort slot berdasarkan jam mulai
            usort($slotsData, fn ($a, $b) => strcmp($a['timeSlot']->start_time, $b['timeSlot']->start_time));

            // Hitung total jam booking di hari ini
            $totalHoursInDay = count($slotsData);

            // Cek diskon HANYA jika booking >= 2 jam
            $discount = null;
            if ($totalHoursInDay >= 2) {
                $firstSlot = $slotsData[0]['timeSlot'];
                $lastSlot = $slotsData[$totalHoursInDay - 1]['timeSlot'];

                // <-- PERBAIKAN: gunakan Carbon::parse yang lebih toleran terhadap format waktu
                $bookingStart = Carbon::parse($firstSlot->start_time);
                $bookingEnd = Carbon::parse($lastSlot->end_time);

                // Cari diskon yang FULL menutupi seluruh blok booking
                $discount = Discount::whereDate('start_date', '<=', $date)
                    ->whereDate('end_date', '>=', $date)
                    ->where(function ($q) use ($isWeekend) {
                        $q->where('day_type', 'all')
                            ->orWhere('day_type', $isWeekend ? 'weekend' : 'weekday');
                    })
                    ->get()
                    ->first(function ($d) use ($bookingStart, $bookingEnd) {
                        // <-- PERBAIKAN: gunakan Carbon::parse juga untuk waktu diskon
                        $discountStart = Carbon::parse($d->start_time);
                        $discountEnd = Carbon::parse($d->end_time);

                        // Diskon hanya berlaku jika seluruh blok booking berada dalam range
                        return $bookingStart->gte($discountStart) && $bookingEnd->lte($discountEnd);
                    });
            }

            // Jika diskon tidak menutupi FULL booking, set null
            if (! $discount) {
                $discount = null;
            }

            // Simpan semua slot hari ini
            foreach ($slotsData as $slotInfo) {
                $timeSlot = $slotInfo['timeSlot'];
                $basePrice = $slotInfo['basePrice'];

                $discountedPrice = $basePrice;
                if ($discount) {
                    if ($discount->type === 'percentage') {
                        $discountedPrice = $basePrice - ($basePrice * $discount->amount / 100);
                    } elseif ($discount->type === 'fixed') {
                        $discountedPrice = max(0, $basePrice - $discount->amount);
                    }
                }

                $selectedSlots[] = [
                    'date' => $date,
                    'time_slot_id' => $slotInfo['slotId'],
                    'price' => $discountedPrice,
                    'original_price' => $basePrice,
                    'discount_id' => $discount?->id,
                ];

                $totalPrice += $discountedPrice;
            }
        }

        if (empty($selectedSlots)) {
            return redirect()->back()->with('error', 'Tidak ada slot valid yang tersedia.');
        }

        session([
            'selected_slots' => $selectedSlots,
            'total_price' => $totalPrice,
        ]);

        return redirect()->route('payment.page');
    }

    public function payment()
    {
        $selectedSlots = session('selected_slots', []);
        $totalPrice = 0;
        $totalOriginalPrice = 0;
        $bookings = [];

        // Group slot per tanggal
        $groupedByDate = collect($selectedSlots)->groupBy('date');

        foreach ($groupedByDate as $date => $slotsInDay) {
            $isWeekend = in_array(Carbon::parse($date)->dayOfWeekIso, [6, 7]);

            // Ambil semua slot TimeSlot
            $slotsData = [];
            foreach ($slotsInDay as $slot) {
                $timeSlot = TimeSlot::find($slot['time_slot_id']);
                if (! $timeSlot) {
                    continue;
                }

                $basePrice = $slot['original_price'] ?? $timeSlot->price;

                $slotsData[] = [
                    'slot' => $slot,
                    'timeSlot' => $timeSlot,
                    'basePrice' => $basePrice,
                ];
            }

            if (empty($slotsData)) {
                continue;
            }

            // Urutkan slot berdasarkan start_time
            usort($slotsData, fn ($a, $b) => strcmp($a['timeSlot']->start_time, $b['timeSlot']->start_time));

            $totalHoursInDay = count($slotsData);

            // Cek diskon per hari, bukan per slot
            $discount = null;
            if ($totalHoursInDay >= 2) {
                $firstSlot = $slotsData[0]['timeSlot'];
                $lastSlot = $slotsData[$totalHoursInDay - 1]['timeSlot'];

                // <-- PERBAIKAN: gunakan Carbon::parse
                $bookingStart = Carbon::parse($firstSlot->start_time);
                $bookingEnd = Carbon::parse($lastSlot->end_time);

                $discount = Discount::whereDate('start_date', '<=', $date)
                    ->whereDate('end_date', '>=', $date)
                    ->where(function ($q) use ($isWeekend) {
                        $q->where('day_type', 'all')
                            ->orWhere('day_type', $isWeekend ? 'weekend' : 'weekday');
                    })
                    ->get()
                    ->first(function ($d) use ($bookingStart, $bookingEnd) {
                        // <-- PERBAIKAN: gunakan Carbon::parse
                        $discountStart = Carbon::parse($d->start_time);
                        $discountEnd = Carbon::parse($d->end_time);

                        // Diskon hanya berlaku jika seluruh blok booking berada dalam range
                        return $bookingStart->gte($discountStart) && $bookingEnd->lte($discountEnd);
                    });
            }

            // Hitung harga setiap slot
            foreach ($slotsData as $slotInfo) {
                $timeSlot = $slotInfo['timeSlot'];
                $basePrice = $slotInfo['basePrice'];

                $priceAfterDiscount = $basePrice;
                if ($discount) {
                    if ($discount->type === 'percentage') {
                        $priceAfterDiscount = round($basePrice * (1 - $discount->amount / 100));
                    } elseif ($discount->type === 'fixed') {
                        $priceAfterDiscount = max(0, $basePrice - $discount->amount);
                    }
                }

                $bookings[] = (object) [
                    'date' => $slotInfo['slot']['date'],
                    'timeSlot' => $timeSlot,
                    'original_price' => $basePrice,
                    'price' => $priceAfterDiscount,
                    'discount' => $discount,
                ];

                $totalOriginalPrice += $basePrice;
                $totalPrice += $priceAfterDiscount;
            }
        }

        $totalDiscount = $totalOriginalPrice - $totalPrice;
        $dpAmount = round($totalPrice * 0.5);
        $finalPrice = $totalPrice;

        return view('booking.payment', compact(
            'bookings',
            'totalOriginalPrice',
            'totalDiscount',
            'finalPrice',
            'dpAmount'
        ));
    }

    public function processPayment(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'payment_type' => 'required|in:dp,full',
            'amount' => 'required|numeric',
            'method' => 'required|in:QRIS,Transfer',
        ]);

        $selectedSlots = session('selected_slots', []);
        if (empty($selectedSlots)) {
            return redirect()->route('booking.index')
                ->with('error', 'Tidak ada booking yang diproses.');
        }

        $user = User::firstOrCreate(
            ['email' => $validated['email']],
            ['name' => $validated['name'], 'phone' => $validated['phone']]
        );

        $paymentService = app(\App\Services\Payments\PaymentService::class);
        $createdPaymentIds = [];
        $expiresAt = now()->addMinutes(10);
        $trxId = 'MOCK-'.strtoupper(uniqid()); // ✅ trx_id tunggal untuk seluruh booking

        try {
            DB::beginTransaction();

            foreach ($selectedSlots as $slot) {
                $timeSlot = \App\Models\TimeSlot::where('id', $slot['time_slot_id'])
                    ->lockForUpdate()
                    ->first();

                $existingBooking = \App\Models\Booking::where('date', $slot['date'])
                    ->where('time_slot_id', $slot['time_slot_id'])
                    ->lockForUpdate()
                    ->first();

                if (
                    $existingBooking &&
                    strtolower($existingBooking->booking_status) === 'booked' &&
                    $existingBooking->expires_at &&
                    $existingBooking->expires_at->isFuture()
                ) {
                    DB::rollBack();

                    return redirect()->route('booking.index')->with('booking_conflict', [
                        'date' => $slot['date'],
                        'time_slot_id' => $slot['time_slot_id'],
                    ]);
                }

                if ($existingBooking) {
                    $existingBooking->update([
                        'user_id' => $user->id,
                        'booking_status' => 'booked',
                        'payment_status' => 'Pending',
                        'total_price' => $slot['price'],
                        'expires_at' => $expiresAt,
                    ]);
                    $booking = $existingBooking->fresh();
                } else {
                    $booking = \App\Models\Booking::create([
                        'user_id' => $user->id,
                        'time_slot_id' => $slot['time_slot_id'],
                        'date' => $slot['date'],
                        'booking_status' => 'booked',
                        'payment_status' => 'Pending',
                        'total_price' => $slot['price'],
                        'expires_at' => $expiresAt,
                    ]);
                }

                $amountPerSlot = $validated['payment_type'] === 'dp'
                    ? round($slot['price'] * 0.5)
                    : (float) $slot['price'];

                // ✅ Gunakan trxId yang sama untuk semua slot
                $resp = $paymentService->createPayment($booking, $amountPerSlot, $validated['method'], $trxId);

                $payment = \App\Models\Payment::create([
                    'booking_id' => $booking->id,
                    'method' => $validated['method'],
                    'amount' => $amountPerSlot,
                    'status' => $resp['status'] ?? 'Pending',
                    'trx_id' => $trxId, // <-- diset manual
                    'payment_date' => null,
                ]);

                $createdPaymentIds[] = $payment->id;
            }

            DB::commit();

            session(['pending_payment_ids' => $createdPaymentIds]);

            return redirect()->route('mock.checkout');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->route('booking.index')
                ->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
