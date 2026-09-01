<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>{{ config('app.name', 'Booking Lapang') }}</title>

    {{-- Google Font --}}
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap"
      rel="stylesheet"
    />

    {{-- Bootstrap CSS --}}
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />

    <style>
      /*
   * CSS Responsif Global
   * Mengoptimalkan layout dasar untuk pengalaman mobile yang lebih baik.
   */
      body {
        font-family: 'Inter', sans-serif;
        background-color: #f4f6f9;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
      }

      header,
      footer {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      }

      .brand-logo {
        font-weight: 700;
        font-size: 1.5rem;
        color: #198754;
        text-decoration: none;
      }
      .brand-logo:hover {
        color: #157347;
      }

      .time-slot-button {
        padding: 1rem;
        border: 2px solid #e0e0e0;
        border-radius: 0.75rem;
        transition: all 0.2s ease-in-out;
        text-align: center;
        white-space: nowrap;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 100%;
        cursor: pointer;
        user-select: none;
        background-color: white;
      }
      .time-slot-button.available:hover {
        background-color: #d1fae5;
        border-color: #198754;
        transform: scale(1.03);
      }
      .time-slot-button.booked {
        background-color: #fee2e2;
        color: #721c24;
        cursor: not-allowed;
      }
      .time-slot-button.selected {
        background-color: #198754 !important;
        color: white;
        border-color: #198754;
      }

      /* Admin Sidebar */
      aside.bg-success {
        width: 260px;
        position: sticky;
        top: 0;
        height: 100vh;
        display: flex;
        flex-direction: column;
        padding: 1.5rem;
        background: linear-gradient(180deg, #198754, #146c43);
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
      }
      aside.bg-success h2 {
        font-weight: 700;
        margin-bottom: 1.5rem;
      }
      aside.bg-success nav a.nav-link {
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        transition: background-color 0.2s ease;
      }
      aside.bg-success nav a.nav-link:hover {
        background-color: rgba(255, 255, 255, 0.15);
      }

      /* Mobile Admin Topbar */
      .mobile-topbar {
        background: linear-gradient(90deg, #198754, #146c43);
        color: white;
        padding: 0.75rem 1rem;
        position: fixed;
        top: 0;
        z-index: 1050;
        width: 100%;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      }
      .mobile-topbar button {
        background: rgba(255, 255, 255, 0.15);
        border: none;
        color: white;
        font-size: 1.25rem;
        padding: 0.4rem 0.75rem;
        border-radius: 0.5rem;
      }
      .mobile-topbar button:hover {
        background: rgba(255, 255, 255, 0.3);
      }

      /* Mobile Sidebar Overlay */
      #sidebarOverlay {
        background: rgba(0, 0, 0, 0.5);
        z-index: 1060;
      }
      #sidebarOverlay .menu-panel {
        background: linear-gradient(180deg, #198754, #146c43);
        padding: 1.5rem;
        color: white;
        height: 100%;
      }
      #sidebarOverlay .menu-panel a.nav-link {
        padding: 0.75rem 0;
        border-radius: 0.5rem;
      }
      #sidebarOverlay .menu-panel a.nav-link:hover {
        background-color: rgba(255, 255, 255, 0.15);
      }

      .active-link {
        background-color: rgba(255, 255, 255, 0.2);
        font-weight: 600;
        border-left: 4px solid #ffc107;
        padding-left: calc(1rem - 4px); /* geser dikit biar rapi */
      }
      .active-link:hover {
        background-color: rgba(255, 255, 255, 0.25);
      }

      /*
   * Perbaikan Responsivitas Admin Layout
   * Menambahkan style khusus untuk mengatasi overflow pada konten utama.
   */
      .admin-content-wrapper {
        flex-grow: 1;
        overflow-x: hidden; /* Mencegah halaman utama scroll horizontal */
        min-width: 0; /* Penting untuk flexbox agar tidak overflow */
      }

      /*
   * FIX: Jeda konten di bawah topbar mobile
   */
      @media (max-width: 991.98px) {
        body.admin-layout {
          padding-top: 56px; /* tinggi mobile topbar */
        }
      }
    </style>
  </head>

  @php
    $isAdminRoute = request()->is('admin/*');
    $isLoginPage = request()->is('admin/login');
    $isLoggedInAsAdmin = Auth::guard('admin')->check();
    $isAdminLayout = $isAdminRoute && ! $isLoginPage && $isLoggedInAsAdmin;

    $hideLoginRoutes = ['admin/login', 'payment', 'mock/checkout'];
    $hideLoginButton = false;

    foreach ($hideLoginRoutes as $route) {
      if (Request::is($route)) {
        $hideLoginButton = true;
        break;
      }
    }
  @endphp

  <body class="{{ $isAdminLayout ? 'admin-layout' : 'public-layout' }}">
    @if (! $isAdminLayout)
      {{-- PUBLIC LAYOUT --}}
      <header class="bg-white">
        <div class="container d-flex justify-content-between align-items-center py-3">
          <a href="/" class="brand-logo">OneInfini</a>
          @if (! $hideLoginButton)
            <a
              href="{{ route('admin.login') }}"
              class="btn btn-success px-4 py-2 rounded-pill shadow-sm"
            >
              Login
            </a>
          @endif
        </div>
      </header>

      <main class="flex-grow-1 container py-4">
        @yield('content')
      </main>

      <footer class="bg-dark text-white text-center py-3 mt-auto">
        <p class="mb-0">&copy; {{ date('Y') }} Booking Lapang. All rights reserved.</p>
      </footer>

      @if (session('invoice_booking_ids'))
        @php
          $bookings = \App\Models\Booking::with(['user', 'timeSlot', 'payment'])
            ->whereIn('id', session('invoice_booking_ids'))
            ->get();

          $firstBooking = $bookings->first();
          $payment = $firstBooking?->payment;
          $user = $firstBooking?->user;

          // Hitung total (jika lunas ambil dari payment->amount, jika belum ambil dari total_price)
          $totalAmount = $bookings->sum(function ($b) {
            return $b->payment_status === 'Lunas' && $b->payment ? $b->payment->amount : $b->total_price;
          });
        @endphp

        <div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Pembayaran Berhasil 🎉</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">
                <p class="mb-2">Terima kasih, pembayaran dan booking Anda berhasil!</p>
                <p class="mb-4">Silakan simpan bukti berikut sebagai referensi:</p>

                @if ($bookings->count() > 0)
                  <div class="border rounded p-3 mb-4 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h6 class="mb-0"><strong>Invoice #{{ $firstBooking->id }}</strong></h6>
                      <a
                        href="{{ route('invoice.download', $firstBooking->id) }}"
                        class="btn btn-sm btn-outline-success"
                      >
                        📄 Download Bukti Booking
                      </a>
                    </div>

                    <p>
                      <strong>Customer:</strong>
                      {{ $user->name ?? '-' }} ({{ $user->email ?? '-' }})
                    </p>

                    <table class="table table-sm">
                      <thead>
                        <tr>
                          <th>Lapangan / Slot</th>
                          <th>Tanggal</th>
                          <th>Waktu</th>
                          <th class="text-end">Jumlah (Rp)</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($bookings as $booking)
                          @php
                            $amount =
                              $booking->payment_status === 'Lunas' && $booking->payment
                                ? $booking->payment->amount
                                : $booking->total_price;
                          @endphp

                          <tr>
                            <td>{{ $booking->timeSlot->name ?? 'Lapangan' }}</td>
                            <td>{{ \Carbon\Carbon::parse($booking->date)->format('d M Y') }}</td>
                            <td>
                              {{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('H:i') }}
                              -
                              {{ \Carbon\Carbon::parse($booking->timeSlot->end_time)->format('H:i') }}
                            </td>
                            <td class="text-end">Rp {{ number_format($amount, 0, ',', '.') }}</td>
                          </tr>
                        @endforeach

                        <tr class="fw-bold">
                          <td colspan="3" class="text-end">Total</td>
                          <td class="text-end">
                            Rp {{ number_format($totalAmount, 0, ',', '.') }}
                          </td>
                        </tr>
                      </tbody>
                    </table>

                    <p class="mb-1">
                      <strong>Metode Pembayaran:</strong>
                      {{ $payment->method ?? '-' }}
                    </p>
                    <p class="mb-1">
                      <strong>No. Transaksi:</strong>
                      {{ $payment->trx_id ?? '-' }}
                    </p>
                    <p class="mb-0">
                      <strong>Status:</strong>
                      <span class="text-success fw-bold">{{ $firstBooking->payment_status }}</span>
                    </p>
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>

        @push('scripts')
          <script>
            document.addEventListener('DOMContentLoaded', function () {
              var invoiceModal = new bootstrap.Modal(document.getElementById('invoiceModal'));
              invoiceModal.show();
            });
          </script>
        @endpush
      @endif

      @if (session('booking_conflict'))
        <div class="modal fade" id="conflictModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Slot Sudah Terisi ❌</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <p>
                  Maaf, slot pada tanggal
                  <strong>
                    {{ \Carbon\Carbon::parse(session('booking_conflict.date'))->format('d M Y') }}
                  </strong>
                  untuk lapangan ID
                  <strong>{{ session('booking_conflict.time_slot_id') }}</strong>
                  sudah/sedang dipesan oleh orang lain.
                </p>
                <p>Silakan pilih slot lain yang masih tersedia atau tunggu 10 menit.</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                  Tutup
                </button>
              </div>
            </div>
          </div>
        </div>

        @push('scripts')
          <script>
            document.addEventListener('DOMContentLoaded', function () {
              var conflictModal = new bootstrap.Modal(document.getElementById('conflictModal'));
              conflictModal.show();
            });
          </script>
        @endpush
      @endif
    @else
      {{-- ADMIN LAYOUT --}}
      <div class="d-flex min-vh-100">
        {{-- SIDEBAR DESKTOP --}}
        <aside class="bg-success text-white d-none d-md-flex flex-column">
          <h2 class="fs-4 fw-bold mb-4">Admin Panel</h2>
          <nav class="nav flex-column mb-4">
            <a
              href="{{ route('admin.dashboard') }}"
              class="nav-link text-white {{ Request::routeIs('admin.dashboard') ? 'active-link' : '' }}"
            >
              📊 Dashboard
            </a>
            <a
              href="{{ route('admin.schedule.edit') }}"
              class="nav-link text-white {{ Request::routeIs('admin.schedule.edit') ? 'active-link' : '' }}"
            >
              🏟 Kelola Lapang
            </a>
            <a
              href="{{ route('admin.manage.discounts') }}"
              class="nav-link text-white {{ Request::routeIs('admin.manage.discounts') ? 'active-link' : '' }}"
            >
              💲 Kelola Diskon
            </a>
            <a
              href="{{ route('admin.users.edit') }}"
              class="nav-link text-white {{ Request::routeIs('admin.users.edit') ? 'active-link' : '' }}"
            >
              👤 Edit User
            </a>
            <a
              href="{{ route('admin.report') }}"
              class="nav-link text-white {{ Request::routeIs('admin.report') ? 'active-link' : '' }}"
            >
              📑 Laporan Invoice
            </a>
          </nav>
          <form action="{{ route('admin.logout') }}" method="POST" class="mt-auto">
            @csrf
            <button type="submit" class="btn btn-danger w-100 shadow-sm">Logout</button>
          </form>
        </aside>

        {{-- MOBILE TOPBAR --}}
        <div class="d-md-none mobile-topbar d-flex justify-content-between align-items-center">
          <button id="menuToggle">☰</button>
          <span class="fw-bold">Admin Panel</span>
        </div>

        {{-- MOBILE SIDEBAR OVERLAY --}}
        <div id="sidebarOverlay" class="d-none position-fixed top-0 start-0 w-100 h-100">
          <div class="menu-panel" style="width: 240px">
            <h2 class="fs-5 fw-bold mb-4">Menu</h2>
            <nav class="nav flex-column">
              <a
                href="{{ route('admin.dashboard') }}"
                class="nav-link text-white {{ Request::routeIs('admin.dashboard') ? 'active-link' : '' }}"
              >
                📊 Dashboard
              </a>
              <a
                href="{{ route('admin.schedule.edit') }}"
                class="nav-link text-white {{ Request::routeIs('admin.schedule.edit') ? 'active-link' : '' }}"
              >
                🏟 Kelola Lapang
              </a>
              <a
                href="{{ route('admin.manage.discounts') }}"
                class="nav-link text-white {{ Request::routeIs('admin.manage.discounts') ? 'active-link' : '' }}"
              >
                💲 Kelola Diskon
              </a>
              <a
                href="{{ route('admin.users.edit') }}"
                class="nav-link text-white {{ Request::routeIs('admin.users.edit') ? 'active-link' : '' }}"
              >
                👤 Edit User
              </a>
              <a
                href="{{ route('admin.report') }}"
                class="nav-link text-white {{ Request::routeIs('admin.report') ? 'active-link' : '' }}"
              >
                📑 Laporan Invoice
              </a>
              <form action="{{ route('admin.logout') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-danger w-100 shadow-sm">Logout</button>
              </form>
            </nav>
          </div>
        </div>

        {{-- MAIN CONTENT --}}
        <div class="admin-content-wrapper">
          <main class="flex-grow-1 container py-4">
            {{-- Flash Messages --}}
            @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="alert"
                  aria-label="Close"
                ></button>
              </div>
            @endif

            @if (session('error'))
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="alert"
                  aria-label="Close"
                ></button>
              </div>
            @endif

            @if (session('expired'))
              <div class="alert alert-warning alert-dismissible fade show" role="alert">
                ⚠️ {{ session('expired') }}
                <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="alert"
                  aria-label="Close"
                ></button>
              </div>
            @endif

            @yield('content')
          </main>
        </div>
      </div>
    @endif

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
      const menuToggle = document.getElementById('menuToggle');
      const sidebarOverlay = document.getElementById('sidebarOverlay');

      if (menuToggle) {
        menuToggle.addEventListener('click', () => {
          sidebarOverlay.classList.toggle('d-none');
        });
      }

      if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', (e) => {
          if (e.target === sidebarOverlay) {
            sidebarOverlay.classList.add('d-none');
          }
        });
      }
    </script>

    @stack('scripts')
  </body>
</html>
