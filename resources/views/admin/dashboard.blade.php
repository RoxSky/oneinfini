@extends('layouts.app')

@section('content')
  <div class="container-fluid px-4">
    <h1 class="fw-bold my-4 text-center text-md-start">🏠 Dashboard Admin</h1>
    <p class="text-center text-md-start mb-5">
      Selamat datang,
      <strong>{{ Auth::guard('admin')->user()->name }}</strong>
      👋
    </p>

    {{-- Ringkasan Utama --}}
    <div class="row g-4 mb-4">
      <!-- Total Booking -->
      <div class="col-md-4">
        <a href="{{ route('admin.report') }}" class="text-decoration-none">
          <div class="card shadow-sm border-0 h-100 dashboard-card">
            <div class="card-body text-center p-4">
              <h2 class="fw-bold text-primary">{{ $totalBookings ?? 0 }}</h2>
              <p class="mb-0 text-muted">Total Booking Bulan Ini</p>
            </div>
          </div>
        </a>
      </div>

      <!-- Pemasukan Bulan Ini -->
      <div class="col-md-4">
        <a href="{{ route('admin.report') }}" class="text-decoration-none">
          <div class="card shadow-sm border-0 h-100 dashboard-card">
            <div class="card-body text-center p-4">
              <h2 class="fw-bold text-success">
                Rp {{ number_format($monthlyIncome ?? 0, 0, ',', '.') }}
              </h2>
              <p class="mb-0 text-muted">Pemasukan Bulan Ini</p>
            </div>
          </div>
        </a>
      </div>

      <!-- Top User -->
      <div class="col-md-4">
        <a href="{{ route('admin.users.edit') }}" class="text-decoration-none">
          <div class="card shadow-sm border-0 h-100 dashboard-card">
            <div class="card-body text-center p-4">
              <h2 class="fw-bold text-warning">{{ $topUser->name ?? '-' }}</h2>
              <p class="mb-0 text-muted">User Dengan Poin Tertinggi</p>
            </div>
          </div>
        </a>
      </div>
    </div>

    {{-- Tabel Ringkasan --}}
    <div class="row g-4">
      <!-- Ringkasan Booking Terbaru -->
      <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
          <div
            class="card-header bg-primary text-white d-flex justify-content-between align-items-center"
          >
            <span>📅 Booking Terbaru</span>
            <a href="{{ route('admin.report') }}" class="text-white small">Lihat Semua</a>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover table-striped mb-0 text-center">
                <thead class="table-light">
                  <tr>
                    <th>Nama</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($latestBookings ?? [] as $booking)
                    <tr>
                      <td>{{ $booking->user->name ?? ($booking->admin->name ?? '-') }}</td>
                      <td>{{ \Carbon\Carbon::parse($booking->date)->format('d M Y') }}</td>
                      <td>
                        <span
                          class="badge bg-{{ $booking->booking_status == 'booked' ? 'success' : 'secondary' }}"
                        >
                          {{ ucfirst($booking->booking_status) }}
                        </span>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="3" class="text-muted py-3">Belum ada booking</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Top 5 User Poin -->
      <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
          <div
            class="card-header bg-warning text-dark d-flex justify-content-between align-items-center"
          >
            <span>🏆 Top 5 User Poin Tertinggi</span>
            <a href="{{ route('admin.users.edit') }}" class="text-dark small">Kelola User</a>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0 text-center">
                <thead class="table-light">
                  <tr>
                    <th>Peringkat</th>
                    <th>Nama</th>
                    <th>Poin</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse (($topUsers ?? collect())->take(5) as $index => $user)
                    <tr>
                      <td>
                        @if ($index == 0)
                          🥇
                        @elseif ($index == 1)
                          🥈
                        @elseif ($index == 2)
                          🥉
                        @else
                          {{ $index + 1 }}
                        @endif
                      </td>
                      <td>{{ $user->name }}</td>
                      <td class="fw-bold text-primary">{{ $user->points }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="3" class="text-muted py-3">Belum ada data user</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @push('styles')
    <style>
      .dashboard-card {
        transition: 0.3s;
        cursor: pointer;
      }
      .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
      }
    </style>
  @endpush
@endsection
