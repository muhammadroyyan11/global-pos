<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $transaction->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 13px; font-weight: 600; background: #f0f0f0; }
        .receipt {
            width: 320px; margin: 24px auto; background: #fff;
            padding: 20px 18px; border-radius: 4px;
            box-shadow: 0 2px 12px rgba(0,0,0,.15);
        }

        /* Header */
        .receipt-header { text-align: center; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 2px dashed #333; }
        .receipt-header .logo { font-size: 28px; margin-bottom: 4px; }
        .receipt-header h2 { font-size: 18px; font-weight: 900; color: #e85d04; letter-spacing: 1px; }
        .receipt-header .tagline { font-size: 11px; font-weight: 700; color: #555; margin-top: 3px; }
        .receipt-header .address { font-size: 10px; font-weight: 600; color: #777; margin-top: 4px; line-height: 1.5; }
        .receipt-header .datetime { font-size: 11px; font-weight: 700; color: #333; margin-top: 6px; }

        /* Info */
        .receipt-info { margin-bottom: 12px; font-size: 12px; font-weight: 700; }
        .receipt-info div { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .receipt-info span:first-child { color: #555; }
        .receipt-info span:last-child { color: #111; }

        /* Divider */
        .divider { border: none; border-top: 2px dashed #333; margin: 10px 0; }
        .divider-solid { border: none; border-top: 2px solid #333; margin: 10px 0; }

        /* Items */
        .items { padding: 4px 0; }
        .item { margin-bottom: 8px; }
        .item-name { font-weight: 900; font-size: 13px; color: #111; }
        .item-bundle-note { font-size: 10px; font-weight: 600; color: #888; margin-top: 1px; }
        .item-detail { display: flex; justify-content: space-between; font-size: 12px; font-weight: 700; color: #333; margin-top: 2px; }

        /* Totals */
        .totals { font-size: 12px; font-weight: 700; }
        .totals .row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .totals .row.discount { color: #28a745; }
        .totals .grand-total {
            display: flex; justify-content: space-between;
            font-size: 16px; font-weight: 900; color: #e85d04;
            padding: 8px 0 6px; margin-top: 2px;
        }
        .totals .row.paid { color: #333; }
        .totals .row.change { font-size: 13px; font-weight: 900; color: #111; }

        /* Footer */
        .receipt-footer { text-align: center; padding-top: 12px; }
        .receipt-footer .thank-you { font-size: 13px; font-weight: 900; color: #e85d04; letter-spacing: .5px; }
        .receipt-footer .sub-msg { font-size: 11px; font-weight: 700; color: #666; margin-top: 3px; }
        .receipt-footer .barcode-area {
            margin: 12px auto 0;
            border: 2px dashed #ccc;
            border-radius: 6px;
            padding: 8px;
            font-size: 10px;
            font-weight: 700;
            color: #aaa;
            letter-spacing: 2px;
        }
        .receipt-footer .barcode-num {
            font-size: 11px; font-weight: 900; color: #555;
            letter-spacing: 3px; margin-top: 4px;
        }
        .receipt-footer .social {
            margin-top: 10px; font-size: 10px; font-weight: 700; color: #888;
        }
        .receipt-footer .powered {
            margin-top: 8px; font-size: 9px; font-weight: 600; color: #bbb;
        }

        /* Print button */
        .btn-print {
            display: block; width: 100%; padding: 12px;
            background: #e85d04; color: #fff; border: none;
            border-radius: 6px; cursor: pointer;
            font-size: 14px; font-weight: 900;
            margin-top: 16px; letter-spacing: .5px;
        }
        .btn-print:hover { background: #c44d00; }

        @media print {
            body { background: #fff; }
            .receipt { box-shadow: none; margin: 0; border-radius: 0; width: 100%; padding: 10px; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>
<div class="receipt">

    {{-- Header --}}
    <div class="receipt-header">
        <h2>{{ strtoupper($store['store_name'] ?? 'TOKO') }}</h2>
        <div class="tagline">{{ $store['store_tagline'] ?? '' }}</div>
        <div class="address">{{ $store['store_address'] ?? '' }}</div>
        @if(!empty($store['store_phone']))
        <div class="address">Telp. {{ $store['store_phone'] }}</div>
        @endif
        <div class="datetime">{{ \Carbon\Carbon::parse($transaction->created_at)->format('d M Y, H:i') }} WIB</div>
    </div>

    {{-- Info Transaksi --}}
    <div class="receipt-info">
        <div><span>No. Invoice</span><span>{{ $transaction->invoice_number }}</span></div>
        <div><span>Kasir</span><span>{{ $transaction->user->name }}</span></div>
        @if($transaction->customer)
        <div><span>Pelanggan</span><span>{{ $transaction->customer->name }}</span></div>
        @endif
        <div><span>Pembayaran</span><span>{{ strtoupper($transaction->payment_method) }}</span></div>
    </div>

    <hr class="divider">

    {{-- Items --}}
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

    <hr class="divider">

    {{-- Totals --}}
    <div class="totals">
        <div class="row"><span>Subtotal</span><span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
        @if($transaction->discount > 0)
        <div class="row discount"><span>Diskon</span><span>- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span></div>
        @endif
        <hr class="divider-solid">
        <div class="grand-total"><span>TOTAL</span><span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span></div>
        <div class="row paid"><span>Dibayar</span><span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span></div>
        <div class="row change"><span>Kembalian</span><span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span></div>
    </div>

    <hr class="divider">

    {{-- Footer --}}
    <div class="receipt-footer">
        <div class="thank-you">*** TERIMA KASIH ***</div>
        <div class="sub-msg">Semoga harimu menyenangkan.</div>
        <div class="sub-msg" style="margin-top:4px;">Simpan struk ini sebagai bukti pembelian.</div>

        <div class="barcode-area">
            <div>||||| |||| ||||| |||| |||||</div>
            <div class="barcode-num">{{ $transaction->invoice_number }}</div>
        </div>

        <div class="social">
            {{ $store['store_social'] ?? '' }}
        </div>
        <div class="powered">-- Powered by POS Jeruk Lokal --</div>
    </div>

        <button class="btn-print" onclick="window.print()">Cetak Struk</button>
</div>
</body>
</html>
