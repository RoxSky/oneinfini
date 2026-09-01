@extends('layouts.app')

@section('content')
  <div class="container my-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title mb-3">Mock Checkout (Simulasi)</h5>

        {{-- Notifikasi --}}

        @if (session('expired'))
          <div class="alert alert-danger">{{ session('expired') }}</div>
        @elseif (session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @elseif (session('fail'))
          <div class="alert alert-warning">{{ session('fail') }}</div>
        @endif

        @if ($payments->isEmpty())
          <p class="text-muted">Tidak ada transaksi yang menunggu.</p>
        @else
          {{-- Timer utama --}}
          <div class="text-center mb-4">
            <h4 class="fw-bold">Selesaikan pembayaran sebelum:</h4>
            <div
              id="mainCountdown"
              data-expiry="{{ $firstExpiry->timestamp }}"
              class="display-4 fw-bold text-danger"
            >
              00:00
            </div>
          </div>

          {{-- Tabel transaksi --}}
          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead class="table-light">
                <tr>
                  <th>Booking ID</th>
                  <th>Tanggal</th>
                  <th>Waktu</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>TRX</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($payments as $p)
                  <tr>
                    <td>{{ $p->booking_id }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->booking->date)->format('d M Y') }}</td>
                    <td>
                      @if ($p->booking->timeSlot)
                        {{ \Carbon\Carbon::parse($p->booking->timeSlot->start_time)->format('H:i') }}
                        -
                        {{ \Carbon\Carbon::parse($p->booking->timeSlot->end_time)->format('H:i') }}
                      @endif
                    </td>
                    <td>Rp{{ number_format($p->amount, 0, ',', '.') }}</td>
                    <td>
                      <span class="badge bg-warning text-dark">{{ ucfirst($p->status) }}</span>
                    </td>
                    <td><code>{{ $p->trx_id ?? '-' }}</code></td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center text-muted">Belum ada transaksi.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          {{-- Tombol simulasi --}}
          <div class="d-flex gap-2 mt-3">
            <form method="POST" action="{{ route('mock.confirm') }}">
              @csrf
              <input type="hidden" name="action" value="success" />
              <button type="submit" class="btn btn-success">Simulasi Berhasil</button>
            </form>
            <form method="POST" action="{{ route('mock.confirm') }}">
              @csrf
              <input type="hidden" name="action" value="fail" />
              <button type="submit" class="btn btn-outline-danger">Simulasi Gagal</button>
            </form>
            <form method="POST" action="{{ route('mock.expire') }}">
              @csrf
              <button type="submit" class="btn btn-dark">Simulasi Expired</button>
            </form>
          </div>
        @endif

        <hr class="my-4" />
        <p class="text-muted mb-0">
          <strong>Catatan:</strong>
          Ini hanya simulasi. Klik
          <em>“Simulasi Berhasil”</em>
          untuk mengubah Payment →
          <strong>Success</strong>
          , Booking →
          <strong>Paid/DP</strong>
          .
        </p>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  {{-- Countdown Timer --}}
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const countdownEl = document.getElementById('mainCountdown');
      if (countdownEl) {
        const expiry = parseInt(countdownEl.dataset.expiry) * 1000;

        function updateCountdown() {
          const now = new Date().getTime();
          const distance = expiry - now;

          if (distance <= 0) {
            countdownEl.textContent = '00:00';
            clearInterval(timer);
            if (!window.hasRedirected) {
              window.hasRedirected = true;
              alert('Booking Expired! Waktu pembayaran habis.');
              window.location.href = '{{ route('mock.expire') }}';
            }
            return;
          }

          const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
          const seconds = Math.floor((distance % (1000 * 60)) / 1000);
          countdownEl.textContent =
            `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }

        updateCountdown();
        const timer = setInterval(updateCountdown, 1000);
      }

      // Trigger modal kalau ada invoice
      @if(session('invoice_booking_ids'))
        var invoiceModal = new bootstrap.Modal(document.getElementById('invoiceModal'));
        invoiceModal.show();
      @endif
    });
  </script>
@endpush
