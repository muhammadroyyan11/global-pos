@extends('layouts.admin')
@section('title', 'Transaksi')
@section('page-title', 'Transaksi')

@section('content')

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px;">
    <div class="card" style="margin:0;">
        <div class="card-body" style="padding:16px;">
            <div style="font-size:.8rem;color:#888;margin-bottom:4px;">Total Transaksi</div>
            <div style="font-size:1.5rem;font-weight:800;color:var(--primary);">{{ $totalTrx }}</div>
        </div>
    </div>
    <div class="card" style="margin:0;">
        <div class="card-body" style="padding:16px;">
            <div style="font-size:.8rem;color:#888;margin-bottom:4px;">Total Pendapatan</div>
            <div style="font-size:1.3rem;font-weight:800;color:#28a745;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="card" style="margin:0;">
        <div class="card-body" style="padding:16px;">
            <div style="font-size:.8rem;color:#888;margin-bottom:4px;">Total Diskon</div>
            <div style="font-size:1.3rem;font-weight:800;color:#dc3545;">Rp {{ number_format($totalDiscount, 0, ',', '.') }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fa-solid fa-receipt" style="color:var(--primary);"></i> Daftar Transaksi</h5>
    </div>
    <div class="card-body" style="padding-bottom:0;">

        {{-- Quick Range Buttons --}}
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px;">
            @foreach(['today'=>'Hari Ini','yesterday'=>'Kemarin','7days'=>'7 Hari','30days'=>'30 Hari','month'=>'Bulan Ini'] as $val=>$label)
            <a href="{{ route('admin.transactions.index', array_merge(request()->except(['range','date_from','date_to','page']), ['range'=>$val])) }}"
               class="btn btn-sm {{ request('range') === $val ? 'btn-primary' : 'btn-secondary' }}">
                {{ $label }}
            </a>
            @endforeach
            <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm {{ !request('range') && !request('date_from') ? 'btn-primary' : 'btn-secondary' }}">Semua</a>
        </div>

        {{-- Filter Form --}}
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <input type="text" name="search" class="form-control" placeholder="No. Invoice..." value="{{ request('search') }}" style="max-width:180px;">
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" style="max-width:150px;">
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" style="max-width:150px;">
            <select name="status" class="form-control" style="max-width:140px;">
                <option value="">Semua Status</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-search"></i> Filter</button>

            {{-- Export Excel --}}
            <a href="{{ route('admin.transactions.export', request()->all()) }}"
               class="btn btn-sm"
               style="background:#1d6f42;color:#fff;display:flex;align-items:center;gap:6px;">
                <i class="fa-solid fa-file-excel"></i> Export Excel
            </a>
        </form>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Kasir</th>
                    <th>Item</th>
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
                    <td style="font-size:.78rem;color:#555;max-width:220px;">
                        @foreach($trx->items as $item)
                            <div>{{ $item->product_name }} <span style="color:#aaa;">×{{ $item->quantity }}</span></div>
                        @endforeach
                    </td>
                    <td>Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                    <td><span class="badge badge-info">{{ strtoupper($trx->payment_method) }}</span></td>
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
