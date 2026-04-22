<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $transaction->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 12px; background: #f5f5f5; }
        .receipt {
            width: 300px; margin: 20px auto; background: #fff;
            padding: 16px; border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,.1);
        }
        .receipt-header { text-align: center; margin-bottom: 12px; border-bottom: 1px dashed #ccc; padding-bottom: 10px; }
        .receipt-header h2 { font-size: 16px; color: #e85d04; }
        .receipt-header p { font-size: 11px; color: #666; }
        .receipt-info { margin-bottom: 10px; font-size: 11px; }
        .receipt-info div { display: flex; justify-content: space-between; margin-bottom: 2px; }
        .items { border-top: 1px dashed #ccc; border-bottom: 1px dashed #ccc; padding: 8px 0; margin-bottom: 8px; }
        .item { margin-bottom: 6px; }
        .item-name { font-weight: bold; }
        .item-detail { display: flex; justify-content: space-between; color: #555; }
        .totals { margin-bottom: 10px; }
        .totals div { display: flex; justify-content: space-between; margin-bottom: 3px; font-size: 11px; }
        .totals .grand-total { font-size: 14px; font-weight: bold; border-top: 1px dashed #ccc; padding-top: 6px; margin-top: 4px; color: #e85d04; }
        .receipt-footer { text-align: center; font-size: 11px; color: #888; border-top: 1px dashed #ccc; padding-top: 10px; }
        .btn-print { display: block; width: 100%; padding: 10px; background: #e85d04; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; margin-top: 12px; }
        @media print {
            body { background: #fff; }
            .receipt { box-shadow: none; margin: 0; border-radius: 0; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>
<div class="receipt">
    <div class="receipt-header">
        <h2>🍊 Jeruk Lokal</h2>
        <p>No Sugar No Water — 100% Pure Orange</p>
        <p style="margin-top:4px;font-size:10px;">{{ date('d M Y H:i') }}</p>
    </div>

    <div class="receipt-info">
        <div><span>Invoice</span><span>{{ $transaction->invoice_number }}</span></div>
        <div><span>Kasir</span><span>{{ $transaction->user->name }}</span></div>
        @if($transaction->customer)
        <div><span>Pelanggan</span><span>{{ $transaction->customer->name }}</span></div>
        @endif
        <div><span>Bayar</span><span>{{ strtoupper($transaction->payment_method) }}</span></div>
    </div>

    <div class="items">
        @foreach($transaction->items as $item)
        <div class="item">
            <div class="item-name">{{ $item->product_name }}</div>
            <div class="item-detail">
                <span>{{ $item->quantity }} × Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <div class="totals">
        <div><span>Subtotal</span><span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
        @if($transaction->discount > 0)
        <div><span>Diskon</span><span>- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span></div>
        @endif
        <div class="grand-total"><span>TOTAL</span><span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span></div>
        <div><span>Bayar</span><span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span></div>
        <div><span>Kembalian</span><span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span></div>
    </div>

    <div class="receipt-footer">
        <p>Terima kasih sudah berbelanja!</p>
        <p>Semoga harimu menyenangkan 🍊</p>
    </div>

    <button class="btn-print" onclick="window.print()">🖨️ Cetak Struk</button>
</div>
</body>
</html>
