<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>Bukti Pembayaran Booking</title>
  </head>
  <body style="font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px">
    <div
      style="
        max-width: 600px;
        margin: auto;
        background: #ffffff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
      "
    >
      <h2 style="color: #333; margin-bottom: 10px">Bukti Pembayaran Booking Lapangan</h2>

      @php
        $firstBooking = $booking ?? $bookings->first();
        $user = $firstBooking?->user;
      @endphp

      <p>
        Halo
        <strong>{{ $user->name ?? 'Pelanggan' }}</strong>
        ,
      </p>
      <p>Terima kasih telah melakukan booking. Berikut adalah detail transaksi Anda:</p>

      <p style="margin-bottom: 10px">
        <strong>ID Transaksi:</strong>
        {{ $trxId ?? '-' }}
        <br />
        <strong>Status Pembayaran:</strong>
        <span style="color: {{ $payment->status === 'Success' ? 'green' : 'red' }}">
          {{ ucfirst($payment->status) }}
        </span>
        <br />
        <strong>Tanggal Pembayaran:</strong>
        {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->translatedFormat('d F Y H:i') : '-' }}
      </p>

      <table style="width: 100%; border-collapse: collapse; margin: 20px 0">
        <thead>
          <tr>
            <th style="border: 1px solid #ddd; padding: 8px; background: #f2f2f2">
              Lapangan / Slot
            </th>
            <th style="border: 1px solid #ddd; padding: 8px; background: #f2f2f2">Tanggal</th>
            <th style="border: 1px solid #ddd; padding: 8px; background: #f2f2f2">Waktu</th>
            <th style="border: 1px solid #ddd; padding: 8px; background: #f2f2f2">Harga</th>
            <th style="border: 1px solid #ddd; padding: 8px; background: #f2f2f2">Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($bookings as $b)
            @php
              $slot = $b->timeSlot;

              // 🔹 Konversi status agar tampil "Lunas" atau "DP (Sebagian)"
              $statusLabel = match (strtolower($b->payment_status)) {
                'paid' => 'Lunas',
                'dp' => 'DP (Sebagian)',
                default => ucfirst($b->payment_status ?? 'Pending'),
              };

              $statusColor = match (strtolower($b->payment_status)) {
                'paid' => 'green',
                'dp' => 'orange',
                default => 'red',
              };
            @endphp

            <tr>
              <td style="border: 1px solid #ddd; padding: 8px">
                {{ $slot->name ?? '-' }}
              </td>
              <td style="border: 1px solid #ddd; padding: 8px; text-align: center">
                {{ \Carbon\Carbon::parse($b->date)->translatedFormat('d F Y') }}
              </td>
              <td style="border: 1px solid #ddd; padding: 8px; text-align: center">
                {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} -
                {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
              </td>
              <td style="border: 1px solid #ddd; padding: 8px; text-align: right">
                Rp {{ number_format($b->total_price, 0, ',', '.') }}
              </td>
              <td
                style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: center;
                  color: {{ $statusColor }};
                  font-weight: bold;
                "
              >
                {{ strtoupper($statusLabel) }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

      <p style="margin-top: 15px">
        <strong>Total Harga Booking:</strong>
        Rp {{ number_format($total_price ?? 0, 0, ',', '.') }}
      </p>

      <p>
        <strong>Jumlah yang Dibayar:</strong>
        Rp {{ number_format($paid_amount ?? 0, 0, ',', '.') }}
        @php
          $remaining = max(0, ($total_price ?? 0) - ($paid_amount ?? 0));
        @endphp

        @if ($remaining > 0)
          <br />
          <strong>Sisa yang harus dibayar:</strong>
          Rp {{ number_format($remaining, 0, ',', '.') }}
        @endif
      </p>

      <p>
        <strong>Metode Pembayaran:</strong>
        {{ $payment_methods ?? ($payment->method ?? '-') }}
        <br />
      </p>

      {{-- 🔹 STATUS PEMBAYARAN GLOBAL --}}
      <p style="margin-top: 8px">
        <strong>Status Pembayaran:</strong>
        @php
          $overallLabel = match ($overall_payment_status) {
            'Paid' => 'Lunas',
            'DP' => 'DP (Sebagian)',
            default => 'Pending',
          };
          $overallColor = match ($overall_payment_status) {
            'Paid' => 'green',
            'DP' => 'orange',
            default => 'red',
          };
        @endphp

        <span style="color: {{ $overallColor }}; font-weight: bold">
          {{ strtoupper($overallLabel) }}
        </span>
      </p>

      <div style="margin: 20px 0">
        <a
          href="{{ route('invoice.download', $firstBooking->id ?? 0) }}"
          style="
            display: inline-block;
            background: #1a73e8;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
          "
        >
          📄 Download Bukti (PDF)
        </a>
      </div>

      <p>Jika ada pertanyaan, balas email ini atau hubungi admin kami.</p>
      <p>
        Salam hangat,
        <br />
        <strong>{{ config('app.name') }}</strong>
      </p>
    </div>
  </body>
</html>
