@extends('layouts.app')

@section('content')
<div class="container my-4">
    <h2 class="mb-4">Kelola Poin & Pengguna</h2>

    <!-- Kelipatan Poin -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5>Kelipatan Harga per 1 Poin</h5>
            <form action="{{ route('admin.users.updatePricePerPoint') }}" method="POST" class="d-flex">
                @csrf
                <input type="number" name="price_per_point" value="{{ $pricePerPoint }}" class="form-control me-2" min="1" required>
                <button class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>

    <!-- List User -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-3">🏆 Top 5 Poin Terbanyak</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead class="table-warning">
                        <tr>
                            <th>Peringkat</th>
                            <th>Nama</th>
                            <th>Poin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users->take(5) as $index => $user)
                            <tr>
                                <td>
                                    @if($index == 0)
                                        <span class="badge bg-warning text-dark">🥇 1</span>
                                    @elseif($index == 1)
                                        <span class="badge bg-secondary">🥈 2</span>
                                    @elseif($index == 2)
                                        <span class="badge bg-danger">🥉 3</span>
                                    @else
                                        <span class="badge bg-dark">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td>{{ $user->name }}</td>
                                <td class="fw-bold text-primary">{{ $user->points }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                <h5 class="mb-2 mb-md-0">Daftar Lengkap User & Poin</h5>
                <input type="text" id="userSearch" class="form-control w-100 w-md-50" placeholder="🔍 Cari nama, email, atau No HP...">
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center" id="userTable">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No HP</th>
                            <th>Poin</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone }}</td>
                                <td>
                                    <span class="fw-bold text-primary">{{ $user->points }}</span>
                                </td>
                                <td>
                                    <form action="{{ route('admin.users.updatePoints', $user->id) }}" method="POST" class="d-flex justify-content-center flex-wrap">
                                        @csrf
                                        <input type="number" name="points" class="form-control form-control-sm w-50 me-2 mb-2 mb-md-0" value="{{ $user->points }}" min="0">
                                        <button class="btn btn-success btn-sm">Update</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('userSearch').addEventListener('keyup', function() {
    let value = this.value.toLowerCase();
    document.querySelectorAll('#userTable tbody tr').forEach(function(row) {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(value) ? '' : 'none';
    });
});
</script>
@endpush

@endsection
