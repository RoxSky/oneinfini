<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>Invoice #{{ $trxId ?? $booking->id }}</title>
    <style>
      body {
        font-family:
          DejaVu Sans,
          sans-serif;
        font-size: 13px;
        color: #222;
        background: #f9f9f9;
        margin: 0;
        padding: 20px;
      }
      .container {
        max-width: 800px;
        margin: auto;
        background: #fff;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
      }
      .header {
        text-align: center;
        margin-bottom: 20px;
      }
      table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
      }
      th,
      td {
        border: 1px solid #ddd;
        padding: 8px;
      }
      th {
        background: #f3f3f3;
      }
      .text-right {
        text-align: right;
      }
      .status-paid {
        color: green;
        font-weight: bold;
      }
      .status-dp {
        color: orange;
        font-weight: bold;
      }
      .status-pending {
        color: #999;
        font-weight: bold;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="header">
        <h2>Bukti Pembayaran Booking Lapangan</h2>
        <div>
          Nomor Transaksi:
          <strong>#{{ $trxId ?? "-" }}</strong>
        </div>
      </div>

      <div style="margin-bottom: 15px">
        <strong>Customer:</strong>
        <br />
        {{ $booking->user->name ?? "-" }}
        <br />
        {{ $booking->user->email ?? "-" }}
        <br />
        {{ $booking->user->phone ?? "-" }}
        <br />
      </div>

      @php
        // Ambil semua slot untuk transaksi ini (jika dikirim dari controller/email)
        $slotList = isset($slots) && count($slots) ? $slots : collect(isset($booking->timeSlot) ? [$booking->timeSlot] : []);

        // Nominal pembayaran aktual
        $paidAmount = $payment->amount ?? 0;
        $displayAmount = $paidAmount > 0 ? $paidAmount : $total_price ?? ($booking->total_price ?? 0);
      @endphp

      <table>
        <thead>
          <tr>
            <th>No</th>
            <th>Lapangan / Slot</th>
            <th>Tanggal</th>
            <th>Waktu</th>
            <th class="text-right">Harga (Rp)</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($slotList as $index => $slot)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $slot->name ?? "Lapangan" }}</td>
              <td>{{ \Carbon\Carbon::parse($booking->date)->format("d M Y") }}</td>
              <td>
                {{ \Carbon\Carbon::parse($slot->start_time)->format("H:i") }}
                -
                {{ \Carbon\Carbon::parse($slot->end_time)->format("H:i") }}
              </td>
              <td class="text-right">Rp {{ number_format($slot->price ?? 0, 0, ",", ".") }}</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <th colspan="4" class="text-right">Total Harga</th>
            <th class="text-right">Rp {{ number_format($displayAmount, 0, ",", ".") }}</th>
          </tr>
        </tfoot>
      </table>

      <p style="margin-top: 15px">
        <strong>Metode Pembayaran:</strong>
        {{ $payment->method ?? "-" }}
        <br />
        <strong>No. Transaksi:</strong>
        {{ $payment->trx_id ?? "-" }}
        <br />
        <strong>Tanggal Pembayaran:</strong>
        {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format("d M Y H:i") : "-" }}
      </p>

      <p style="text-align: right; margin-top: 15px">
        <strong>Status:</strong>
        <span
          class="@if ($booking->payment_status === "Paid")
              status-paid
          @elseif ($booking->payment_status === "DP")
              status-dp
          @else
              status-pending
          @endif"
        >
          {{ strtoupper($booking->payment_status) }}
        </span>
      </p>

      <hr />
      <p style="font-size: 12px; color: #666">
        Terima kasih telah melakukan booking. Simpan bukti ini sebagai konfirmasi pembayaran Anda.
      </p>
    </div>
  </body>
</html>
