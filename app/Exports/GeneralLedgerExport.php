<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
class GeneralLedgerExport implements FromView, WithTitle, WithColumnWidths, WithStyles, WithColumnFormatting
{
    protected $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function view(): View
    {
        return view('accounts.reports.general_ledger_excel', $this->data);
    }
    
    public function title(): string
    {
        return 'General Ledger';
    }
    
    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 18,
            'C' => 15,
            'D' => 35,
            'E' => 15,
            'F' => 15,
            'G' => 15,
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        $sheet->getStyle('A2:G2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        $sheet->getStyle('A3:G3')->applyFromArray([
            'font' => [
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        // Format number columns (remove currency symbol)
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('E:F')
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');
        
        return [];
    }
        /**
     * Format numeric columns as simple decimal numbers
     */
    public function columnFormats(): array
    {
          return [
        'E' => NumberFormat::FORMAT_NUMBER_00,
        'F' => NumberFormat::FORMAT_NUMBER_00,
        'G' => NumberFormat::FORMAT_NUMBER_00,
    ];
    }
}