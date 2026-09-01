<!DOCTYPE html>
<html>
<head>
    <title>Laporan Keuangan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: center; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h3 style="text-align:center">Laporan Keuangan Mini Soccer</h3>

    <table>
        <thead>
            <tr>
                <th>Tanggal & Jam</th>
                <th>Nama Pemesan</th>
                <th>Status</th>
                <th>Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $trx)
            @php
                $amount = $trx->payment_status == 'DP'
                    ? $trx->total_price * 0.5
                    : $trx->total_price;
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($trx->date)->format('d M Y') }} - {{ $trx->timeSlot->start_time ?? '-' }}</td>
                <td>{{ $trx->user->name ?? 'Guest' }}</td>
                <td>{{ $trx->payment_status }}</td>
                <td>Rp{{ number_format($amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="3"><b>Total</b></td>
                <td><b>Rp{{ number_format($totalIncome, 0, ',', '.') }}</b></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
