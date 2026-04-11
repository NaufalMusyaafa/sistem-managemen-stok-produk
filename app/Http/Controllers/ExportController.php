<?php

namespace App\Http\Controllers;

use App\Models\StockHistory;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use App\Services\InventoryService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    private InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Export all warehouses — one sheet per warehouse.
     */
    public function exportAll()
    {
        $warehouses = Warehouse::orderBy('name')->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // remove default blank sheet

        foreach ($warehouses as $index => $warehouse) {
            $items = WarehouseProduct::withoutGlobalScopes()
                ->with('product')
                ->where('warehouse_id', $warehouse->id)
                ->get();

            $sheet = $spreadsheet->createSheet($index);
            $sheet->setTitle(mb_substr($warehouse->name, 0, 31)); // Excel max sheet name 31 chars

            $this->buildSheet($sheet, $warehouse, $items);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Stok_Semua_Gudang_' . now()->format('Ymd_His') . '.xlsx';
        return $this->streamExcel($spreadsheet, $filename);
    }

    /**
     * Export a single warehouse by ID.
     */
    public function exportWarehouse(int $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $items = WarehouseProduct::withoutGlobalScopes()
            ->with('product')
            ->where('warehouse_id', $id)
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($warehouse->name, 0, 31));

        $this->buildSheet($sheet, $warehouse, $items);

        $safeName = preg_replace('/[^a-zA-Z0-9_]/', '_', $warehouse->name);
        $filename = 'Stok_' . $safeName . '_' . now()->format('Ymd_His') . '.xlsx';
        return $this->streamExcel($spreadsheet, $filename);
    }

    /**
     * Build a formatted sheet for the given warehouse and its items.
     */
    private function buildSheet(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        Warehouse $warehouse,
        $items
    ): void {
        // ── Title rows ──────────────────────────────────────────────────────
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', $warehouse->name);
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D9488']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', $warehouse->location . '   •   Diekspor: ' . now()->translatedFormat('d F Y, H:i'));
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '374151']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0FDFA']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Header row ───────────────────────────────────────────────────────
        $headers = ['No', 'Nama Produk', 'SKU', 'Satuan', 'Stok Saat Ini', 'ROP', 'Status', 'Terakhir Update'];
        $headerCols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

        foreach ($headers as $i => $header) {
            $col = $headerCols[$i];
            $sheet->setCellValue("{$col}3", $header);
        }

        $sheet->getStyle('A3:H3')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '134E4A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '99F6E4']]],
        ]);

        // ── Data rows ────────────────────────────────────────────────────────
        $row = 4;
        $rowNum = 0;

        foreach ($items as $item) {
            $rowNum++;

            $rop = $this->inventoryService->calculateROP(
                (float) $item->avg_daily_usage,
                (int) $item->lead_time,
                (int) $item->safety_stock
            );

            $lastHistory = StockHistory::where('warehouse_product_id', $item->id)
                ->latest('created_at')
                ->first();

            $lastUpdated = $lastHistory
                ? $lastHistory->created_at->format('d/m/Y')
                : 'Belum ada';

            $statusLabel = match ($item->status) {
                'normal'   => 'Normal',
                'low_stock' => 'Low Stock',
                'on_order' => 'On Order',
                default    => $item->status,
            };

            $sheet->setCellValue("A{$row}", $rowNum);
            $sheet->setCellValue("B{$row}", $item->product->name);
            $sheet->setCellValue("C{$row}", $item->product->sku);
            $sheet->setCellValue("D{$row}", $item->product->unit);
            $sheet->setCellValue("E{$row}", (int) $item->current_stock);
            $sheet->setCellValue("F{$row}", $rop);
            $sheet->setCellValue("G{$row}", $statusLabel);
            $sheet->setCellValue("H{$row}", $lastUpdated);

            // Zebra striping
            $bgColor = ($rowNum % 2 === 0) ? 'F0FDF4' : 'FFFFFF';
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1FAE5']]],
            ]);

            // Center alignment for some columns
            $sheet->getStyle("A{$row}:A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Color status
            $statusColor = match ($item->status) {
                'normal'    => '065F46',
                'low_stock' => '991B1B',
                'on_order'  => '1E40AF',
                default     => '374151',
            };
            $sheet->getStyle("G{$row}")->getFont()->setColor(
                (new \PhpOffice\PhpSpreadsheet\Style\Color("FF{$statusColor}"))
            );
            $sheet->getStyle("G{$row}")->getFont()->setBold(true);

            $row++;
        }

        // ── Summary row ──────────────────────────────────────────────────────
        if ($rowNum > 0) {
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", "Total: {$rowNum} produk");
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                'font' => ['bold' => true, 'italic' => true, 'color' => ['rgb' => '065F46']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
            ]);
        }

        // ── Column widths ────────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(14);
        $sheet->getColumnDimension('H')->setWidth(18);

        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(3)->setRowHeight(20);
    }

    /**
     * Stream the spreadsheet as a download response.
     */
    private function streamExcel(Spreadsheet $spreadsheet, string $filename)
    {
        $writer = new Xlsx($spreadsheet);

        $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
        $writer->save($tempFile);

        return Response::download($tempFile, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ])->deleteFileAfterSend(true);
    }
}
