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
            match ($range) {
                'today'   => $query->whereDate('created_at', $today),
                'yesterday' => $query->whereDate('created_at', $today->copy()->subDay()),
                '7days'   => $query->whereBetween('created_at', [$today->copy()->subDays(6)->startOfDay(), now()]),
                '30days'  => $query->whereBetween('created_at', [$today->copy()->subDays(29)->startOfDay(), now()]),
                'month'   => $query->whereMonth('created_at', $today->month)->whereYear('created_at', $today->year),
                default   => null,
            };
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
        $rangeLabel = $request->range ? match($request->range) {
            'today'     => 'Hari Ini (' . Carbon::today()->format('d M Y') . ')',
            'yesterday' => 'Kemarin (' . Carbon::yesterday()->format('d M Y') . ')',
            '7days'     => '7 Hari Terakhir',
            '30days'    => '30 Hari Terakhir',
            'month'     => 'Bulan Ini (' . Carbon::now()->format('M Y') . ')',
            default     => 'Custom',
        } : ($request->date_from && $request->date_to
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

        // ── Column Widths ──
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(45);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(14);
        $sheet->getColumnDimension('G')->setWidth(16);
        $sheet->getColumnDimension('H')->setWidth(14);
        $sheet->getStyle('D5:D' . ($row - 1))->getAlignment()->setWrapText(true);

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
