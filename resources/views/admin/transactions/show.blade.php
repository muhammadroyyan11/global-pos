@extends('layouts.admin')
@section('title', 'Detail Transaksi')
@section('page-title', 'Detail Transaksi')

@section('content')
<div style="max-width:700px;">
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header">
            <h5><i class="fa-solid fa-receipt" style="color:var(--primary);"></i> {{ $transaction->invoice_number }}</h5>
            <span class="badge {{ $transaction->status === 'completed' ? 'badge-success' : 'badge-danger' }}">
                {{ ucfirst($transaction->status) }}
            </span>
        </div>
        <div class="card-body">
            <div class="form-row" style="margin-bottom:16px;">
                <div>
                    <div style="font-size:.8rem;color:#888;">Kasir</div>
                    <strong>{{ $transaction->user->name }}</strong>
                </div>
                <div>
                    <div style="font-size:.8rem;color:#888;">Pelanggan</div>
                    <strong>{{ $transaction->customer?->name ?? 'Umum' }}</strong>
                </div>
                <div>
                    <div style="font-size:.8rem;color:#888;">Metode Bayar</div>
                    <span class="badge badge-info">{{ strtoupper($transaction->payment_method) }}</span>
                </div>
                <div>
                    <div style="font-size:.8rem;color:#888;">Tanggal</div>
                    <strong>{{ $transaction->created_at->format('d M Y H:i') }}</strong>
                </div>
            </div>

            <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
                <thead>
                    <tr style="background:#f8f9fa;">
                        <th style="padding:10px;text-align:left;border-bottom:2px solid #e9ecef;">Produk</th>
                        <th style="padding:10px;text-align:center;border-bottom:2px solid #e9ecef;">Qty</th>
                        <th style="padding:10px;text-align:right;border-bottom:2px solid #e9ecef;">Harga</th>
                        <th style="padding:10px;text-align:right;border-bottom:2px solid #e9ecef;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->items as $item)
                    <tr>
                        <td style="padding:10px;border-bottom:1px solid #f0f0f0;">{{ $item->product_name }}</td>
                        <td style="padding:10px;text-align:center;border-bottom:1px solid #f0f0f0;">{{ $item->quantity }}</td>
                        <td style="padding:10px;text-align:right;border-bottom:1px solid #f0f0f0;">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td style="padding:10px;text-align:right;border-bottom:1px solid #f0f0f0;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top:16px;text-align:right;">
                <div style="display:flex;justify-content:flex-end;gap:40px;margin-bottom:6px;">
                    <span style="color:#888;">Subtotal</span>
                    <span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($transaction->discount > 0)
                <div style="display:flex;justify-content:flex-end;gap:40px;margin-bottom:6px;">
                    <span style="color:#888;">Diskon</span>
                    <span style="color:#dc3545;">- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($transaction->tax > 0)
                <div style="display:flex;justify-content:flex-end;gap:40px;margin-bottom:6px;">
                    <span style="color:#888;">Pajak</span>
                    <span>Rp {{ number_format($transaction->tax, 0, ',', '.') }}</span>
                </div>
                @endif
                <div style="display:flex;justify-content:flex-end;gap:40px;margin-bottom:6px;font-size:1.1rem;font-weight:700;border-top:2px solid #e9ecef;padding-top:8px;">
                    <span>Total</span>
                    <span style="color:var(--primary);">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:40px;margin-bottom:4px;">
                    <span style="color:#888;">Bayar</span>
                    <span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:40px;">
                    <span style="color:#888;">Kembalian</span>
                    <span style="color:#28a745;font-weight:600;">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:10px;">
        <a href="{{ route('pos.receipt', $transaction) }}" class="btn btn-primary" target="_blank">
            <i class="fa-solid fa-print"></i> Cetak Struk
        </a>
        <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>
@endsection
