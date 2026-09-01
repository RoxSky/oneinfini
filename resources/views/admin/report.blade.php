@extends('layouts.app')

@section('content')
  <div class="container">
    <h3 class="mb-4 fw-bold">Laporan Keuangan Mini Soccer</h3>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.report') }}" class="row g-3 mb-4">
      <div class="col-md-3">
        <label>Filter</label>
        <select name="filter" class="form-select">
          <option value="">Tampilkan Random 20 Data</option>
          <option value="daily" {{ request('filter') == 'daily' ? 'selected' : '' }}>
            Harian
          </option>
          <option value="weekly" {{ request('filter') == 'weekly' ? 'selected' : '' }}>
            Mingguan
          </option>
          <option value="monthly" {{ request('filter') == 'monthly' ? 'selected' : '' }}>
            Bulanan
          </option>
        </select>
      </div>
      <div class="col-md-3">
        <label>Tanggal / Bulan</label>
        <input
          type="date"
          name="date"
          class="form-control"
          value="{{ request('date', now()->format('Y-m-d')) }}"
        />
      </div>
      <div class="col-md-3">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-primary w-100">Terapkan Filter</button>
      </div>
      <div class="col-md-3 d-flex align-items-end justify-content-end">
        <a href="{{ route('admin.report.pdf', request()->all()) }}" class="btn btn-danger me-2">
          Export PDF
        </a>
      </div>
    </form>

    {{-- Tabel Laporan --}}
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
          <tr>
            <th>Tanggal & Jam</th>
            <th>Nama Pemesan</th>
            <th>Status</th>
            <th>Jumlah (Rp)</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($transactions as $trx)
            <tr>
              <td>
                {{ \Carbon\Carbon::parse($trx->date)->format('d M Y') }} -
                {{ $trx->timeSlot->start_time ?? '-' }}
              </td>
              <td>{{ $trx->user->name ?? ($trx->admin->name ?? 'Guest') }}</td>
              <td>
                <span
                  class="badge bg-{{ $trx->payment_status == 'Paid' ? 'success' : 'warning' }}"
                >
                  {{ $trx->payment_status }}
                </span>
              </td>
              <td>
                @php
                  $amount = $trx->payment_status == 'DP' ? $trx->total_price * 0.5 : $trx->total_price;
                @endphp

                Rp{{ number_format($amount, 0, ',', '.') }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center">Tidak ada data</td>
            </tr>
          @endforelse
          <tr class="table-success fw-bold">
            <td colspan="3" class="text-end">Total</td>
            <td>Rp{{ number_format($totalIncome, 0, ',', '.') }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-3">
      {{ $transactions->appends(request()->query())->links() }}
    </div>
  </div>
@endsection
