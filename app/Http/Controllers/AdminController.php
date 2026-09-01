<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Discount;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // Menampilkan halaman login admin
    public function showLogin()
    {
        return view('admin.login');
    }

    // Proses login admin
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard')->with('success', 'Berhasil login sebagai admin.');
        }

        return back()->withErrors([
            'name' => 'Nama atau password salah.',
        ]);
    }

    // Halaman dashboard admin
    public function dashboard()
    {
        // Total booking bulan ini
        $totalBookings = Booking::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->whereIn('payment_status', ['DP', 'Paid'])
            ->count();

        // Total pemasukan bulan ini (DP = 50%)
        $monthlyIncome = Booking::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->whereIn('payment_status', ['DP', 'Paid'])
            ->get()
            ->sum(function ($trx) {
                return $trx->payment_status === 'DP'
                    ? $trx->total_price * 0.5
                    : $trx->total_price;
            });

        // Booking terbaru (5 terakhir)
        // Booking terbaru (5 terakhir, valid + manual admin)
        $latestBookings = Booking::with('user', 'admin')
            ->where(function ($q) {
                $q->where('booking_status', 'booked')
                    ->whereIn('payment_status', ['DP', 'Paid']);
            })
            ->orWhere(function ($q) {
                $q->whereNotNull('admin_id')
                    ->where('booking_status', 'booked');
            })
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        // Top 5 user berdasarkan poin
        $topUsers = User::orderByDesc('points')->take(5)->get();

        // User dengan poin tertinggi (untuk kartu ringkasan)
        $topUser = $topUsers->first();

        return view('admin.dashboard', compact(
            'totalBookings',
            'monthlyIncome',
            'latestBookings',
            'topUsers',
            'topUser'
        ));
    }

    // Logout admin
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('booking.index')->with('success', 'Anda telah logout.');
    }

    // ============================
    // ✨ Edit Jadwal (Schedule)
    // ============================

    // Tampilkan halaman edit jadwal
    public function editSchedule(Request $request)
    {
        $startOfWeek = $request->week
            ? Carbon::parse($request->week)->startOfWeek()
            : Carbon::now()->startOfWeek();

        $endOfWeek = $startOfWeek->copy()->endOfWeek();

        $dates = collect();
        for ($i = 0; $i < 7; $i++) {
            $dates->push($startOfWeek->copy()->addDays($i));
        }

        $timeSlots = TimeSlot::all();

        // Ambil booking berdasarkan minggu ini
        $bookingsRaw = Booking::whereBetween('date', [$startOfWeek, $endOfWeek])->get();
        $bookings = collect();
        foreach ($bookingsRaw as $booking) {
            $key = $booking->date.'-'.$booking->time_slot_id;
            $bookings->put($key, $booking);
        }

        return view('admin.edit_schedule', [
            'startDate' => $startOfWeek,
            'endDate' => $endOfWeek,
            'previousWeek' => $startOfWeek->copy()->subWeek()->toDateString(),
            'nextWeek' => $startOfWeek->copy()->addWeek()->toDateString(),
            'dates' => $dates,
            'timeSlots' => $timeSlots,
            'bookings' => $bookings,
        ]);
    }

    // Simpan perubahan harga dan status jadwal
    public function updateSchedule(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'time_slot_id' => 'required|exists:time_slots,id',
            'booking_status' => 'required|in:available,booked',
            'price' => 'required|numeric|min:0',
        ]);

        $date = Carbon::parse($validated['date']);
        $bookingStatus = $validated['booking_status'];
        $paymentStatus = $bookingStatus === 'booked' ? 'Paid' : 'Pending';

        // Ambil TimeSlot untuk harga default
        $timeSlot = TimeSlot::findOrFail($validated['time_slot_id']);
        $defaultPrice = $date->isWeekend()
            ? $timeSlot->weekend_price
            : $timeSlot->weekday_price;

        // Cari Booking yang sesuai
        $booking = Booking::where('date', $validated['date'])
            ->where('time_slot_id', $validated['time_slot_id'])
            ->first();

        $adminId = null; // Definisikan variabel terlebih dahulu
        if (Auth::guard('admin')->check()) {
            $adminId = Auth::guard('admin')->id();
        }

        if (! $booking) {
            $booking = new Booking([
                'user_id' => null,
                'date' => $validated['date'],
                'time_slot_id' => $validated['time_slot_id'],
            ]);
        }

        // Simpan harga custom & status
        $booking->total_price = $validated['price'];
        $booking->booking_status = $bookingStatus;
        $booking->payment_status = $paymentStatus;

        // ✨ Tambahkan ID admin yang mengupdate jadwal
        // Catatan: Asumsi kolom 'admin_id' sudah ada di tabel 'bookings'.
        $booking->admin_id = $adminId;

        // 🔹 Jika Available & harga sama dengan default → hapus record agar fallback ke default
        if ($bookingStatus === 'available' && $validated['price'] == $defaultPrice) {
            if ($booking->exists) {
                $booking->delete();
            }
        } else {
            $booking->save();
        }

        return redirect()->back()->with('success', 'Jadwal berhasil diperbarui untuk hari & jam ini saja.');
    }

    public function updateBulkPrice(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|in:Pagi,Siang,Sore,Maghrib,Malam,Midnight',
            'day_type' => 'required|in:weekday,weekend',
            'price' => 'required|numeric|min:0',
        ]);

        $column = $validated['day_type'] === 'weekday' ? 'weekday_price' : 'weekend_price';

        DB::table('time_slots')
            ->where('name', $validated['name'])
            ->update([$column => $validated['price']]);

        return back()->with('success', 'Harga berhasil diperbarui untuk '.$validated['name'].' ('.$validated['day_type'].')');
    }

    public function storeDiscount(Request $request)
    {
        $validated = $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'day_type' => 'required|in:weekday,weekend,all',
            'type' => 'required|in:percentage,fixed',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        Discount::create($validated);

        return back()->with('success', 'Diskon berhasil disimpan.');
    }

    // Halaman manage discounts
    public function manageDiscounts()
    {
        $discounts = Discount::orderBy('start_date', 'desc')->get();

        return view('admin.manage_discounts', compact('discounts'));
    }

    // Hapus diskon
    public function deleteDiscount($id)
    {
        $discount = Discount::findOrFail($id);
        $discount->delete();

        return back()->with('success', 'Diskon berhasil dihapus.');
    }

    public function invoiceReport(Request $request)
    {
        $filter = $request->input('filter'); // 'daily', 'weekly', 'monthly' atau null
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();

        // Query Booking dengan relasi user & timeSlot
        $query = Booking::with(['user', 'timeSlot', 'admin'])
            ->whereIn('payment_status', ['DP', 'Paid']);

        // Filter berdasarkan pilihan admin
        if ($filter === 'daily') {
            $query->whereDate('date', $date);
        } elseif ($filter === 'weekly') {
            $query->whereBetween('date', [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()]);
        } elseif ($filter === 'monthly') {
            $query->whereMonth('date', $date->month)
                ->whereYear('date', $date->year);
        }

        // Urutkan dari terbaru ke terlama (DESC)
        $query->orderBy('date', 'desc');

        // Pagination 20 data
        $transactions = $query->paginate(20);

        // Hitung total income (DP = 50%)
        $totalIncome = $transactions->sum(function ($trx) {
            return $trx->payment_status === 'DP'
                ? $trx->total_price * 0.5
                : $trx->total_price;
        });

        return view('admin.report', compact('transactions', 'totalIncome', 'filter', 'date'));
    }

    public function exportInvoicePdf(Request $request)
    {
        $filter = $request->input('filter'); // 'daily', 'weekly', 'monthly' atau null
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();

        // Ambil data Booking + relasi
        $query = Booking::with(['user', 'timeSlot', 'admin'])
            ->whereIn('payment_status', ['DP', 'Paid']);

        // Filter sesuai pilihan admin
        if ($filter === 'daily') {
            $query->whereDate('date', $date);
        } elseif ($filter === 'weekly') {
            $query->whereBetween('date', [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()]);
        } elseif ($filter === 'monthly') {
            $query->whereMonth('date', $date->month)
                ->whereYear('date', $date->year);
        }

        // Urutkan dari terbaru ke terlama
        $transactions = $query->orderBy('date', 'desc')->get();

        // Hitung total income (DP = 50%)
        $totalIncome = $transactions->sum(function ($trx) {
            return $trx->payment_status === 'DP'
                ? $trx->total_price * 0.5
                : $trx->total_price;
        });

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.report_pdf', [
            'transactions' => $transactions,
            'totalIncome' => $totalIncome,
            'filter' => $filter,
            'date' => $date,
        ])->setPaper('A4', 'portrait');

        return $pdf->download('Invoice_'.$filter.'_'.$date->format('Y-m-d').'.pdf');
    }

    public function editUser()
    {
        $pricePerPoint = DB::table('settings')->where('key', 'price_per_point')->value('value') ?? 100000;
        $users = User::orderBy('points', 'desc')->get();

        return view('admin.edit_user', compact('pricePerPoint', 'users'));
    }

    public function updatePricePerPoint(Request $request)
    {
        $request->validate(['price_per_point' => 'required|numeric|min:1']);

        DB::table('settings')->updateOrInsert(
            ['key' => 'price_per_point'],
            ['value' => $request->price_per_point, 'updated_at' => now()]
        );

        return back()->with('success', 'Kelipatan harga per poin berhasil diperbarui.');
    }

    public function updateUserPoints(Request $request, $id)
    {
        $request->validate(['points' => 'required|integer|min:0']);

        $user = User::findOrFail($id);
        $user->points = $request->points;
        $user->save();

        return back()->with('success', 'Poin user berhasil diperbarui.');
    }
}
