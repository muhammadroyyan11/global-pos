@extends('layouts.admin')
@section('title', 'Transaksi')
@section('page-title', 'Transaksi')

@section('content')
<div class="card">
    <div class="card-header">
        <h5><i class="fa-solid fa-receipt" style="color:var(--primary);"></i> Daftar Transaksi</h5>
    </div>
    <div class="card-body" style="padding-bottom:0;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;">
            <input type="text" name="search" class="form-control" placeholder="No. Invoice..." value="{{ request('search') }}" style="max-width:180px;">
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" style="max-width:160px;">
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" style="max-width:160px;">
            <select name="status" class="form-control" style="max-width:140px;">
                <option value="">Semua Status</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-search"></i> Filter</button>
            <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-rotate"></i></a>
        </form>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Kasir</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Pembayaran</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $trx)
                <tr>
                    <td><strong style="color:var(--primary);">{{ $trx->invoice_number }}</strong></td>
                    <td>{{ $trx->user->name }}</td>
                    <td>{{ $trx->customer?->name ?? 'Umum' }}</td>
                    <td>Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge badge-info">{{ strtoupper($trx->payment_method) }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $trx->status === 'completed' ? 'badge-success' : 'badge-danger' }}">
                            {{ ucfirst($trx->status) }}
                        </span>
                    </td>
                    <td style="font-size:.8rem;">{{ $trx->created_at->format('d M Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.transactions.show', $trx) }}" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        @if($trx->status !== 'cancelled')
                        <form method="POST" action="{{ route('admin.transactions.destroy', $trx) }}" style="display:inline;" onsubmit="return confirm('Batalkan transaksi ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fa-solid fa-ban"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:20px;color:#888;">Belum ada transaksi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body">{{ $transactions->links() }}</div>
</div>
@endsection
