@extends('layouts.app')

@section('content')
  <div class="container-fluid py-4">
    <h2 class="mb-4">Kelola Diskon</h2>

    <!-- Form Tambah Diskon -->
    <div class="card mb-4 shadow-sm">
      <div class="card-header fw-bold">Tambah Diskon Baru</div>
      <div class="card-body">
        <form method="POST" action="{{ route('admin.schedule.discount.store') }}">
          @csrf
          <div class="row g-3">
            <div class="col-12 col-md-6 col-lg-3">
              <label class="form-label">Jam Mulai Diskon</label>
              <input type="time" name="start_time" class="form-control" required />
            </div>
            <div class="col-12 col-md-6 col-lg-3">
              <label class="form-label">Jam Berakhir Diskon</label>
              <input type="time" name="end_time" class="form-control" required />
            </div>
            <div class="col-12 col-md-6 col-lg-3">
              <label class="form-label">Tipe Hari</label>
              <select name="day_type" class="form-select" required>
                <option value="all">Semua</option>
                <option value="weekday">Weekday</option>
                <option value="weekend">Weekend</option>
              </select>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
              <label class="form-label">Tipe Diskon</label>
              <select name="type" class="form-select" required>
                <option value="fixed">Diskon Tetap (Rp)</option>
                <option value="percentage">Persentase (%)</option>
              </select>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
              <label class="form-label">Jumlah Diskon</label>
              <input
                type="number"
                name="amount"
                class="form-control"
                min="0"
                step="0.01"
                required
              />
            </div>
            <div class="col-12 col-md-6 col-lg-3">
              <label class="form-label">Keterangan</label>
              <input type="text" name="description" class="form-control" />
            </div>

            <div class="col-12 col-md-6 col-lg-3">
              <label class="form-label">Mulai Berlaku</label>
              <input type="date" name="start_date" class="form-control" required />
            </div>
            <div class="col-12 col-md-6 col-lg-3">
              <label class="form-label">Berakhir</label>
              <input type="date" name="end_date" class="form-control" required />
            </div>
          </div>

          <div class="mt-3 text-end">
            <button type="submit" class="btn btn-primary">Simpan Diskon</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Daftar Diskon Aktif -->
    @if (isset($discounts) && $discounts->count())
      <div class="card shadow-sm">
        <div class="card-header fw-bold">Daftar Diskon Aktif</div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
              <thead class="table-dark">
                <tr>
                  <th>Jam Berlaku</th>
                  <th class="text-nowrap">Tipe Hari</th>
                  <th>Tipe</th>
                  <th>Jumlah</th>
                  <th>Periode</th>
                  <th>Keterangan</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($discounts as $discount)
                  <tr>
                    <td>
                      {{ \Carbon\Carbon::parse($discount->start_time)->format('H:i') }} -
                      {{ \Carbon\Carbon::parse($discount->end_time)->format('H:i') }}
                    </td>
                    <td>{{ ucfirst($discount->day_type) }}</td>
                    <td>
                      {{ $discount->type === 'percentage' ? 'Persentase' : 'Tetap' }}
                    </td>
                    <td>
                      {{ $discount->type === 'percentage' ? $discount->amount . '%' : 'Rp' . number_format($discount->amount, 0, ',', '.') }}
                    </td>
                    <td>
                      {{ \Carbon\Carbon::parse($discount->start_date)->format('d M Y') }} -
                      {{ \Carbon\Carbon::parse($discount->end_date)->format('d M Y') }}
                    </td>
                    <td>{{ $discount->description ?? '-' }}</td>
                    <td>
                      <form
                        method="POST"
                        action="{{ route('admin.schedule.discount.delete', $discount->id) }}"
                        onsubmit="return confirm('Hapus diskon ini?')"
                      >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                      </form>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    @endif
  </div>
@endsection
