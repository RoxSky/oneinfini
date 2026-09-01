@extends('layouts.app')

@section('content')
  <style>
    /*
     * CSS Responsif dan Sticky Header
     *
     * 1. Container fluid untuk memastikan layout beradaptasi dengan lebar layar.
     * 2. Media queries untuk menumpuk elemen-elemen di luar tabel pada layar kecil.
     * 3. Pengoptimalan gaya tabel dan formulir di dalam sel untuk membuat tabel
     * lebih ringkas dan mudah digulir secara horizontal.
     * 4. Menambahkan properti "sticky" pada header tabel agar tetap terlihat saat di-scroll
     * di dalam container-nya sendiri.
     */

    .container-fluid {
      padding: 1rem;
    }

    .card {
      border-radius: 0.75rem;
      border: none;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .bulk-update-card .card-body {
      padding: 1.5rem;
    }

    .bulk-update-card .form-label {
      font-weight: 600;
      font-size: 0.9rem;
    }

    .bulk-update-card .form-control,
    .bulk-update-card .form-select {
      font-size: 0.9rem;
      padding: 0.5rem 0.75rem;
    }

    .bulk-update-card .btn {
      padding: 0.5rem 1rem;
      font-size: 0.9rem;
      font-weight: bold;
    }

    /* Mengubah navigasi minggu menjadi menumpuk di mobile */
    .week-navigation-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 1.5rem;
    }

    .week-navigation-container .btn {
      flex-grow: 1;
      text-align: center;
    }

    @media (max-width: 767.98px) {
      .week-navigation-container {
        flex-direction: column;
        gap: 0.75rem;
      }

      .week-navigation-container .btn {
        width: 100%;
      }
    }

    /* Tabel Jadwal: Memastikan bisa digulir horizontal */
    .table-responsive {
      width: 100%;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch; /* Meningkatkan scrolling di iOS */
      margin-bottom: 0;
    }

    /* === Perbaikan Utama Sticky Header === */
    /* Buat wadah gulir baru untuk tabel dengan tinggi maksimum */
    .table-scroll-container {
      max-height: 70vh; /* Tinggi maksimum, sesuaikan jika perlu */
      overflow-y: auto; /* Memungkinkan gulir vertikal */
    }

    .edit-schedule-table {
      width: 100%;
      min-width: 700px; /* Mempertahankan lebar minimum untuk desktop */
      border-collapse: collapse;
    }

    /* Terapkan sticky pada thead yang kini berada di dalam konteks gulir yang benar */
    .edit-schedule-table thead {
      position: sticky;
      top: 0;
      z-index: 10;
      background-color: #f8f9fa; /* Background agar tidak transparan */
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .edit-schedule-table thead th {
      border-bottom: 2px solid #dee2e6; /* Garis bawah untuk pemisah */
    }

    /* Gaya dasar untuk tabel (berlaku di semua ukuran layar) */
    .edit-schedule-table th,
    .edit-schedule-table td {
      white-space: nowrap;
      vertical-align: middle;
      padding: 0.25rem;
      border: 1px solid #dee2e6;
      text-align: center;
    }

    .edit-schedule-table thead th {
      padding: 0.5rem 0.25rem;
      font-size: 0.8rem;
    }

    .edit-schedule-table tbody td {
      padding: 0.25rem;
    }

    .slot-form-container {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      gap: 0.25rem;
      padding: 0.5rem 0.25rem;
      height: 100%;
    }

    .slot-form-container.booked {
      background-color: #fff1f1;
    }
    .slot-form-container.available {
      background-color: #f0fdf4;
    }

    /* Mengoptimalkan ukuran elemen di dalam sel tabel khusus untuk mobile */
    @media (max-width: 767.98px) {
      .edit-schedule-table {
        min-width: 500px; /* Mengurangi min-width di mobile agar tidak terlalu lebar */
      }

      .edit-schedule-table th,
      .edit-schedule-table td {
        padding: 0.15rem; /* Padding lebih kecil */
      }

      .edit-schedule-table thead th {
        padding: 0.3rem 0.15rem; /* Padding lebih kecil */
        font-size: 0.7rem; /* Font header lebih kecil */
      }

      .slot-form-container {
        gap: 0.15rem; /* Jarak antar elemen form lebih kecil */
        padding: 0.2rem 0.1rem;
      }

      .slot-form-container .form-control,
      .slot-form-container .form-select {
        font-size: 0.65rem; /* Font input dan select lebih kecil */
        padding: 0.1rem 0.2rem;
      }

      .slot-form-container .btn {
        font-size: 0.6rem; /* Font tombol lebih kecil */
        padding: 0.1rem 0.2rem;
      }
    }
  </style>

  <div class="container-fluid mb-3">
    <h1 class="fw-bold my-2 text-center text-md-start">Kelola Jadwal Lapangan</h1>

    <!-- Form Bulk Update Harga -->
    <div class="card mb-4 shadow-sm bulk-update-card">
      <div class="card-header fw-bold bg-white">Edit Harga Berdasarkan Nama Waktu & Hari</div>
      <div class="card-body">
        <form method="POST" action="{{ route('admin.schedule.bulkprice.update') }}">
          @csrf
          <div class="row g-3">
            <div class="col-12 col-sm-6 col-md-3">
              <label class="form-label">Nama Waktu</label>
              <select name="name" class="form-select" required>
                <option value="Pagi">Pagi (07.00 - 11.00)</option>
                <option value="Siang">Siang (11.00 - 15.00)</option>
                <option value="Sore">Sore (15.00 - 17.00)</option>
                <option value="Maghrib">Maghrib (17.00 - 19.00)</option>
                <option value="Malam">Malam (19.00 - 21.00)</option>
                <option value="Midnight">Midnight (21.00 - 23.00)</option>
              </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
              <label class="form-label">Jenis Hari</label>
              <select name="day_type" class="form-select" required>
                <option value="weekday">Hari Biasa (Senin - Jumat)</option>
                <option value="weekend">Akhir Pekan (Sabtu - Minggu)</option>
              </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
              <label class="form-label">Harga Baru</label>
              <input type="number" name="price" class="form-control" required />
            </div>
            <div class="col-12 col-sm-6 col-md-3 d-flex align-items-end">
              <button type="submit" class="btn btn-warning w-100 fw-bold">Update Harga</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Navigasi Minggu -->
    <div class="week-navigation-container">
      <a
        href="{{ route('admin.schedule.edit', ['week' => $previousWeek ?? 0]) }}"
        class="btn btn-outline-secondary"
      >
        ← Minggu Sebelumnya
      </a>
      <span class="fw-bold text-center">
        {{ $startDate->format('d M') }} - {{ $endDate->format('d M Y') }}
      </span>
      <a
        href="{{ route('admin.schedule.edit', ['week' => $nextWeek ?? 0]) }}"
        class="btn btn-outline-secondary"
      >
        Minggu Berikutnya →
      </a>
    </div>

    @php
      \Carbon\Carbon::setLocale('id');
    @endphp

    <!-- Tabel Edit Harga -->
    <div class="card shadow-sm schedule-card">
      <div class="table-responsive">
        <!-- Wadah gulir vertikal baru untuk tabel -->
        <div class="table-scroll-container">
          <table class="table table-bordered text-center align-middle edit-schedule-table mb-0">
            <thead class="table-light">
              <tr>
                @foreach ($dates as $date)
                  <th>
                    <div class="fw-bold">{{ strtoupper($date->isoFormat('dddd')) }}</div>
                    <div class="small text-muted">{{ $date->format('d M Y') }}</div>
                  </th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @foreach ($timeSlots as $slot)
                <tr>
                  @foreach ($dates as $date)
                    @php
                      $key = $date->toDateString() . '-' . $slot->id;
                      $booking = $bookings->get($key);
                      $isBooked = optional($booking)->booking_status === 'booked';
                      $isWeekend = in_array($date->dayOfWeekIso, [6, 7]);
                      $slotPrice = optional($booking)->total_price ?? ($isWeekend ? $slot->weekend_price : $slot->weekday_price);
                    @endphp

                    <td>
                      <div class="slot-form-container {{ $isBooked ? 'booked' : 'available' }}">
                        <form
                          method="POST"
                          action="{{ route('admin.schedule.update') }}"
                          class="d-flex flex-column gap-1"
                        >
                          @csrf
                          <input type="hidden" name="date" value="{{ $date->toDateString() }}" />
                          <input type="hidden" name="time_slot_id" value="{{ $slot->id }}" />

                          <div class="fw-bold text-nowrap small text-dark">
                            {{ \Carbon\Carbon::parse($slot->start_time)->format('H.i') }} -
                            {{ \Carbon\Carbon::parse($slot->end_time)->format('H.i') }}
                          </div>

                          <input
                            type="number"
                            name="price"
                            value="{{ number_format($slotPrice, 0, '', '') }}"
                            class="form-control text-center"
                          />

                          <select name="booking_status" class="form-select">
                            <option value="available" {{ $isBooked ? '' : 'selected' }}>
                              Available
                            </option>
                            <option value="booked" {{ $isBooked ? 'selected' : '' }}>
                              Booked
                            </option>
                          </select>

                          <button type="submit" class="btn btn-sm btn-success fw-bold">
                            Update
                          </button>
                        </form>
                      </div>
                    </td>
                  @endforeach
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection
