<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TransactionController extends Controller
{
    private function applyFilters(Request $request)
    {
        $query = Transaction::with(['user', 'customer', 'items']);

        // Quick range
        $range = $request->range;
        if ($range) {
            $today = Carbon::today();
            switch ($range) {
                case 'today':     $query->whereDate('created_at', $today); break;
                case 'yesterday': $query->whereDate('created_at', $today->copy()->subDay()); break;
                case '7days':     $query->whereBetween('created_at', [$today->copy()->subDays(6)->startOfDay(), now()]); break;
                case '30days':    $query->whereBetween('created_at', [$today->copy()->subDays(29)->startOfDay(), now()]); break;
                case 'month':     $query->whereMonth('created_at', $today->month)->whereYear('created_at', $today->year); break;
            }
        } else {
            if ($request->date_from) $query->whereDate('created_at', '>=', $request->date_from);
            if ($request->date_to)   $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->search) $query->where('invoice_number', 'like', '%' . $request->search . '%');
        if ($request->status) $query->where('status', $request->status);

        return $query;
    }

    public function index(Request $request)
    {
        $query        = $this->applyFilters($request);
        $transactions = (clone $query)->latest()->paginate(15)->withQueryString();
        $completed    = (clone $query)->where('status', 'completed');
        $totalRevenue = (clone $completed)->sum('total');
        $totalDiscount= (clone $completed)->sum('discount');
        $totalTrx     = (clone $completed)->count();

        return view('admin.transactions.index', compact('transactions', 'totalRevenue', 'totalDiscount', 'totalTrx'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'customer', 'items.product']);
        return view('admin.transactions.show', compact('transaction'));
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->update(['status' => 'cancelled']);
        return redirect()->route('admin.transactions.index')->with('success', 'Transaksi dibatalkan.');
    }

    public function export(Request $request)
    {
        $query        = $this->applyFilters($request)->where('status', 'completed');
        $transactions = $query->latest()->get();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Transaksi');

        // ── Header Info ──
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'LAPORAN TRANSAKSI - ' . strtoupper(\App\Models\StoreSetting::get('store_name', 'TOKO')));
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells('A2:H2');
        $rangeLabel = $request->range ? (function($r) {
            switch($r) {
                case 'today':     return 'Hari Ini (' . Carbon::today()->format('d M Y') . ')';
                case 'yesterday': return 'Kemarin (' . Carbon::yesterday()->format('d M Y') . ')';
                case '7days':     return '7 Hari Terakhir';
                case '30days':    return '30 Hari Terakhir';
                case 'month':     return 'Bulan Ini (' . Carbon::now()->format('M Y') . ')';
                default:          return 'Custom';
            }
        })($request->range) : ($request->date_from && $request->date_to
            ? $request->date_from . ' s/d ' . $request->date_to
            : 'Semua Waktu');
        $sheet->setCellValue('A2', 'Periode: ' . $rangeLabel . '   |   Dicetak: ' . now()->format('d M Y H:i'));
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 10, 'italic' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Column Headers ──
        $headers = ['No', 'Invoice', 'Kasir', 'Item', 'Subtotal', 'Diskon', 'Total', 'Metode Bayar'];
        $cols    = ['A','B','C','D','E','F','G','H'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue($cols[$i] . '4', $h);
        }
        $sheet->getStyle('A4:H4')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E85D04']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ]);

        // ── Data Rows ──
        $row         = 5;
        $grandTotal  = 0;
        $grandDisc   = 0;

        foreach ($transactions as $i => $trx) {
            $itemNames = $trx->items->map(fn($it) => $it->product_name . ' ×' . $it->quantity)->join(', ');
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $trx->invoice_number);
            $sheet->setCellValue('C' . $row, $trx->user->name);
            $sheet->setCellValue('D' . $row, $itemNames);
            $sheet->setCellValue('E' . $row, (float) $trx->subtotal);
            $sheet->setCellValue('F' . $row, (float) $trx->discount);
            $sheet->setCellValue('G' . $row, (float) $trx->total);
            $sheet->setCellValue('H' . $row, strtoupper($trx->payment_method));

            $sheet->getStyle("E{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $bg = $i % 2 === 0 ? 'FFFFFF' : 'FFF5F0';
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'EEEEEE']]],
            ]);

            $grandTotal += $trx->total;
            $grandDisc  += $trx->discount;
            $row++;
        }

        // ── Summary ──
        $row++;
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL (' . $transactions->count() . ' transaksi)');
        $sheet->setCellValue("E{$row}", $grandTotal + $grandDisc);
        $sheet->setCellValue("F{$row}", $grandDisc);
        $sheet->setCellValue("G{$row}", $grandTotal);
        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
            'font'    => ['bold' => true],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF0E6']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'E85D04']]],
        ]);
        $sheet->getStyle("E{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0');

        // ── Column Widths Sheet 1 ──
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(45);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(14);
        $sheet->getColumnDimension('G')->setWidth(16);
        $sheet->getColumnDimension('H')->setWidth(14);
        $sheet->getStyle('D5:D' . ($row - 1))->getAlignment()->setWrapText(true);

        // ══════════════════════════════════════════
        // SHEET 2 — Detail Item Terjual
        // ══════════════════════════════════════════
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Detail Item Terjual');

        // Header info
        $sheet2->mergeCells('A1:G1');
        $sheet2->setCellValue('A1', 'DETAIL ITEM TERJUAL - ' . strtoupper(\App\Models\StoreSetting::get('store_name', 'TOKO')));
        $sheet2->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet2->mergeCells('A2:G2');
        $sheet2->setCellValue('A2', 'Periode: ' . $rangeLabel . '   |   Dicetak: ' . now()->format('d M Y H:i'));
        $sheet2->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 10, 'italic' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Header kolom
        $h2 = ['No', 'Invoice', 'Nama Item', 'Jenis', 'Qty (Cup)', 'Harga Satuan', 'Subtotal'];
        foreach ($h2 as $ci => $label) {
            $sheet2->setCellValue(chr(65 + $ci) . '4', $label);
        }
        $sheet2->getStyle('A4:G4')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D6F42']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ]);

        // Data item
        $r2       = 5;
        $no       = 1;
        $itemTotals = []; // untuk rekap per nama item

        foreach ($transactions as $trx) {
            // Hitung proporsi diskon per transaksi ke tiap item
            $trxSubtotal = (float) $trx->subtotal;
            $trxDiscount = (float) $trx->discount;

            foreach ($trx->items as $item) {
                $isBundle = is_null($item->product_id);

                if ($isBundle) {
                    // Cari bundle berdasarkan nama (strip " xN" di akhir)
                    $bundleName = preg_replace('/ x\d+$/', '', $item->product_name);
                    $bundle     = \App\Models\Bundle::with('items.product')
                                    ->where('name', $bundleName)->first();

                    // Qty bundle yang dibeli (misal "TM 3 x2" berarti 2 bundle)
                    preg_match('/ x(\d+)$/', $item->product_name, $m);
                    $bundleQty = isset($m[1]) ? (int)$m[1] : $item->quantity;

                    if ($bundle && $bundle->items->count()) {
                        // Expand ke produk-produk di dalam bundle
                        foreach ($bundle->items as $bi) {
                            $totalQty      = $bi->quantity * $bundleQty;
                            $itemSubtotal  = (float) $item->subtotal;
                            $discProp      = $trxSubtotal > 0 ? ($itemSubtotal / $trxSubtotal) * $trxDiscount : 0;
                            $effectiveSub  = $itemSubtotal - $discProp;

                            // Proporsi harga per produk dalam bundle
                            $bundleNormal  = (float) $bundle->normal_price;
                            $productNormal = $bi->product ? (float)$bi->product->price * $bi->quantity * $bundleQty : 0;
                            $productShare  = $bundleNormal > 0 ? ($productNormal / $bundleNormal) : (1 / $bundle->items->count());
                            $productEffSub = $effectiveSub * $productShare;
                            $effectivePrice = $totalQty > 0 ? $productEffSub / $totalQty : 0;

                            $productName = $bi->product->name ?? $bi->product_id;

                            $sheet2->setCellValue('A' . $r2, $no++);
                            $sheet2->setCellValue('B' . $r2, $trx->invoice_number);
                            $sheet2->setCellValue('C' . $r2, $productName . ' (via ' . $bundleName . ')');
                            $sheet2->setCellValue('D' . $r2, 'Bundle');
                            $sheet2->setCellValue('E' . $r2, $totalQty);
                            $sheet2->setCellValue('F' . $r2, round($effectivePrice));
                            $sheet2->setCellValue('G' . $r2, round($productEffSub));

                            $sheet2->getStyle("F{$r2}:G{$r2}")->getNumberFormat()->setFormatCode('#,##0');
                            $sheet2->getStyle("E{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            $bg2 = ($no % 2 === 0) ? 'FFFFFF' : 'F0FFF4';
                            $sheet2->getStyle("A{$r2}:G{$r2}")->applyFromArray([
                                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg2]],
                                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'EEEEEE']]],
                            ]);

                            $key = $productName;
                            if (!isset($itemTotals[$key])) $itemTotals[$key] = ['qty' => 0, 'subtotal' => 0, 'jenis' => 'Produk'];
                            $itemTotals[$key]['qty']      += $totalQty;
                            $itemTotals[$key]['subtotal'] += $productEffSub;

                            $r2++;
                        }
                        continue; // skip baris bundle asli
                    }
                }

                // Produk biasa
                $itemSubtotal  = (float) $item->subtotal;
                $discProp      = $trxSubtotal > 0 ? ($itemSubtotal / $trxSubtotal) * $trxDiscount : 0;
                $effectiveSub  = $itemSubtotal - $discProp;
                $effectivePrice = $item->quantity > 0 ? $effectiveSub / $item->quantity : 0;

                $sheet2->setCellValue('A' . $r2, $no++);
                $sheet2->setCellValue('B' . $r2, $trx->invoice_number);
                $sheet2->setCellValue('C' . $r2, $item->product_name);
                $sheet2->setCellValue('D' . $r2, 'Produk');
                $sheet2->setCellValue('E' . $r2, $item->quantity);
                $sheet2->setCellValue('F' . $r2, round($effectivePrice));
                $sheet2->setCellValue('G' . $r2, round($effectiveSub));

                $sheet2->getStyle("F{$r2}:G{$r2}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet2->getStyle("E{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $bg2 = ($no % 2 === 0) ? 'FFFFFF' : 'F0FFF4';
                $sheet2->getStyle("A{$r2}:G{$r2}")->applyFromArray([
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg2]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'EEEEEE']]],
                ]);

                $key = $item->product_name;
                if (!isset($itemTotals[$key])) $itemTotals[$key] = ['qty' => 0, 'subtotal' => 0, 'jenis' => 'Produk'];
                $itemTotals[$key]['qty']      += $item->quantity;
                $itemTotals[$key]['subtotal'] += $effectiveSub;

                $r2++;
            }
        }

        // ── Rekap per Item ──
        $r2 += 2;
        $sheet2->mergeCells("A{$r2}:G{$r2}");
        $sheet2->setCellValue("A{$r2}", 'REKAP TOTAL PER ITEM');
        $sheet2->getStyle("A{$r2}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D6F42']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $r2++;

        $recapHeaders = ['No', 'Nama Item', 'Jenis', 'Total Qty (Cup)', '', 'Harga Rata-rata', 'Total Pendapatan'];
        foreach ($recapHeaders as $ci => $label) {
            $sheet2->setCellValue(chr(65 + $ci) . $r2, $label);
        }
        $sheet2->getStyle("A{$r2}:G{$r2}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2D8653']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $r2++;

        $rno = 1;
        arsort($itemTotals); // urutkan berdasarkan subtotal terbesar
        foreach ($itemTotals as $name => $data) {
            $avgPrice = $data['qty'] > 0 ? round($data['subtotal'] / $data['qty']) : 0;
            $sheet2->setCellValue('A' . $r2, $rno++);
            $sheet2->setCellValue('B' . $r2, $name);
            $sheet2->setCellValue('C' . $r2, $data['jenis']);
            $sheet2->setCellValue('D' . $r2, $data['qty']);
            $sheet2->setCellValue('F' . $r2, $avgPrice);
            $sheet2->setCellValue('G' . $r2, round($data['subtotal']));
            $sheet2->getStyle("F{$r2}:G{$r2}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet2->getStyle("D{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $bg3 = ($rno % 2 === 0) ? 'FFFFFF' : 'F0FFF4';
            $sheet2->getStyle("A{$r2}:G{$r2}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg3]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
            ]);
            $r2++;
        }

        // Column widths sheet 2
        $sheet2->getColumnDimension('A')->setWidth(5);
        $sheet2->getColumnDimension('B')->setWidth(35);
        $sheet2->getColumnDimension('C')->setWidth(12);
        $sheet2->getColumnDimension('D')->setWidth(14);
        $sheet2->getColumnDimension('E')->setWidth(5);
        $sheet2->getColumnDimension('F')->setWidth(18);
        $sheet2->getColumnDimension('G')->setWidth(20);

        // Aktifkan sheet 1 saat dibuka
        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'laporan-transaksi-' . now()->format('Ymd-His') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
