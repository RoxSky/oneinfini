@extends('layouts.app')

@section('content')
  <div class="container mb-4">
    <!-- Ringkasan Booking -->
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h5 class="card-title">Ringkasan Booking</h5>
        <ul class="list-group">
          @foreach ($bookings as $booking)
            <li class="list-group-item">
              <div class="d-flex justify-content-between">
                <span>
                  <strong>{{ \Carbon\Carbon::parse($booking->date)->format('d M Y') }}</strong>
                  <br />
                  {{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('H:i') }} -
                  {{ \Carbon\Carbon::parse($booking->timeSlot->end_time)->format('H:i') }}
                </span>
                <div class="text-end">
                  <div>
                    Harga Normal: Rp{{ number_format($booking->original_price, 0, ',', '.') }}
                  </div>
                  @if ($booking->discount)
                    <div class="text-danger">
                      Diskon:

                      @if ($booking->discount->type === 'percentage')
                        {{ $booking->discount->amount }}%
                      @else
                        Rp{{ number_format($booking->discount->amount, 0, ',', '.') }}
                      @endif
                    </div>
                  @endif
                </div>
              </div>
            </li>
          @endforeach

          <li class="list-group-item d-flex justify-content-between">
            <span class="fw-bold">Total Diskon</span>
            <span class="fw-bold text-danger">
              - Rp{{ number_format($totalDiscount, 0, ',', '.') }}
            </span>
          </li>
          <li class="list-group-item d-flex justify-content-between fw-bold text-success">
            <span>Total Setelah Diskon</span>
            <span>Rp{{ number_format($finalPrice, 0, ',', '.') }}</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Form Data Diri -->
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Data Diri Pemesan</h5>
        <form id="paymentForm" method="POST" action="{{ route('payment.process') }}">
          @csrf
          <input type="hidden" name="payment_type" id="paymentType" />
          <input type="hidden" name="amount" id="paymentAmount" />

          <div class="mb-3">
            <label for="name" class="form-label">Nama Lengkap</label>
            <input
              type="text"
              name="name"
              id="name"
              class="form-control"
              required
              placeholder="Nama Pemesan"
            />
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Alamat Email</label>
            <input
              type="email"
              name="email"
              id="email"
              class="form-control"
              required
              placeholder="email@example.com"
            />
          </div>
          <div class="mb-3">
            <label for="phone" class="form-label">Nomor HP</label>
            <input
              type="text"
              name="phone"
              id="phone"
              class="form-control"
              required
              placeholder="08xxxx"
            />
          </div>
          <!-- Pilihan Metode Pembayaran -->
          <div class="mb-3">
            <label class="form-label">Metode Pembayaran</label>
            <select class="form-select" name="method" id="method" required>
              <option value="">-- Pilih Metode --</option>
              <option value="QRIS">QRIS (BNI)</option>
              <option value="Transfer">Virtual Account (BNI)</option>
            </select>
          </div>

          <!-- Terms & Conditions -->
          <div class="mb-3 border rounded p-3 bg-light">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="agreeTerms" />
              <label class="form-check-label" for="agreeTerms">
                Saya telah membaca dan menyetujui
                <a
                  href="#"
                  data-bs-toggle="modal"
                  data-bs-target="#termsModal"
                  class="fw-bold text-decoration-underline"
                >
                  Syarat dan Ketentuan
                </a>
                yang berlaku.
              </label>
            </div>
          </div>

          <div class="d-flex justify-content-between mb-3">
            <button
              type="button"
              class="btn btn-outline-primary w-50 me-2"
              onclick="submitPayment('dp', '{{ $dpAmount }}')"
              disabled
              id="btnDp"
            >
              Bayar DP (50%)
              <br />
              <small>Rp{{ number_format($dpAmount, 0, ',', '.') }}</small>
            </button>
            <button
              type="button"
              class="btn btn-outline-success w-50 ms-2"
              onclick="submitPayment('full', '{{ $finalPrice }}')"
              disabled
              id="btnFull"
            >
              Bayar Full
              <br />
              <small>Rp{{ number_format($finalPrice, 0, ',', '.') }}</small>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Syarat & Ketentuan -->
  <div
    class="modal fade"
    id="termsModal"
    tabindex="-1"
    aria-labelledby="termsModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="termsModalLabel">Syarat & Ketentuan</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <ul>
            <li>Pengguna lapangan diharapkan datang tepat waktu sesuai jadwal.</li>
            <li>Pengguna lapangan meninggalkan lapangan sesuai durasi yang ditentukan.</li>
            <li>
              Segala kerusakan fasilitas One Infini MiniSoccer yang disebabkan oleh kesalahan
              pengguna (dikenakan denda).
            </li>
            <li>Reschedule minimal H-7 hari dari tanggal main.</li>
            <li>One Infini tidak memberlakukan kebijakan Refund.</li>
            <li>
              Pengguna wajib meninggalkan lapangan 5 menit sebelum waktu berakhir dari jam yang
              disewa.
            </li>
          </ul>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    // Aktifkan tombol pembayaran hanya jika T&C dicentang
    document.getElementById('agreeTerms').addEventListener('change', function () {
      const isChecked = this.checked;
      document.getElementById('btnDp').disabled = !isChecked;
      document.getElementById('btnFull').disabled = !isChecked;
    });

    function submitPayment(type, amount) {
      const name = document.getElementById('name').value.trim();
      const email = document.getElementById('email').value.trim();
      const phone = document.getElementById('phone').value.trim();
      const method = document.getElementById('method').value;
      const agree = document.getElementById('agreeTerms').checked;

      if (!name || !email || !phone) {
        alert('Silakan lengkapi data diri terlebih dahulu.');
        return;
      }

      if (!method) {
        alert('Pilih metode pembayaran terlebih dahulu.');
        return;
      }

      if (!agree) {
        alert('Anda harus menyetujui syarat & ketentuan sebelum melanjutkan.');
        return;
      }

      document.getElementById('paymentType').value = type;
      document.getElementById('paymentAmount').value = amount;
      document.getElementById('paymentForm').submit();
    }
  </script>
@endpush
