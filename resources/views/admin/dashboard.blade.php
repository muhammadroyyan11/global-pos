@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#e85d04;">
            <i class="fa-solid fa-money-bill-wave"></i>
        </div>
        <div class="stat-info">
            <h3>Rp {{ number_format($todaySales, 0, ',', '.') }}</h3>
            <p>Penjualan Hari Ini</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#2d6a4f;">
            <i class="fa-solid fa-chart-line"></i>
        </div>
        <div class="stat-info">
            <h3>Rp {{ number_format($monthlySales, 0, ',', '.') }}</h3>
            <p>Penjualan Bulan Ini</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#0077b6;">
            <i class="fa-solid fa-receipt"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $todayTransactions }}</h3>
            <p>Transaksi Hari Ini</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#7b2d8b;">
            <i class="fa-solid fa-box"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $totalProducts }}</h3>
            <p>Total Produk <span style="color:#dc3545;font-size:.75rem;">({{ $lowStockProducts }} stok rendah)</span></p>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
    <!-- Chart -->
    <div class="card" style="grid-column: 1 / -1;">
        <div class="card-header">
            <h5><i class="fa-solid fa-chart-bar" style="color:var(--primary);"></i> Penjualan 7 Hari Terakhir</h5>
        </div>
        <div class="card-body">
            <canvas id="salesChart" height="80"></canvas>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <!-- Recent Transactions -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fa-solid fa-clock-rotate-left" style="color:var(--primary);"></i> Transaksi Terbaru</h5>
            <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransactions as $trx)
                    <tr>
                        <td>
                            <a href="{{ route('admin.transactions.show', $trx) }}" style="color:var(--primary);font-weight:600;">
                                {{ $trx->invoice_number }}
                            </a>
                            <div style="font-size:.75rem;color:#888;">{{ $trx->created_at->format('d M H:i') }}</div>
                        </td>
                        <td>Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $trx->status === 'completed' ? 'badge-success' : ($trx->status === 'cancelled' ? 'badge-danger' : 'badge-warning') }}">
                                {{ ucfirst($trx->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align:center;color:#888;padding:20px;">Belum ada transaksi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Products -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fa-solid fa-trophy" style="color:#f4a261;"></i> Produk Terlaris</h5>
        </div>
        <div class="card-body">
            @forelse($topProducts as $i => $item)
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
                <div style="width:28px;height:28px;border-radius:50%;background:{{ ['#e85d04','#f4a261','#2d6a4f','#0077b6','#7b2d8b'][$i] }};color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;flex-shrink:0;">
                    {{ $i + 1 }}
                </div>
                <div style="flex:1;">
                    <div style="font-weight:600;font-size:.875rem;">{{ $item->product_name }}</div>
                    <div style="font-size:.75rem;color:#888;">{{ $item->total_qty }} terjual</div>
                </div>
                <div style="font-weight:600;color:var(--primary);font-size:.875rem;">
                    Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                </div>
            </div>
            @empty
            <p style="text-align:center;color:#888;">Belum ada data</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_column($salesChart, 'date')) !!},
        datasets: [{
            label: 'Penjualan (Rp)',
            data: {!! json_encode(array_column($salesChart, 'total')) !!},
            backgroundColor: 'rgba(232,93,4,.7)',
            borderColor: '#e85d04',
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: v => 'Rp ' + new Intl.NumberFormat('id').format(v)
                }
            }
        }
    }
});
</script>
@endpush
