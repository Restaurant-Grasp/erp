<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Contracts\View\View;

class TrialBalanceExport implements FromView, WithTitle, WithColumnWidths, WithStyles
{
    protected $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function view(): View
    {
        return view('accounts.reports.trial_balance_excel', $this->data);
    }
    
    public function title(): string
    {
        return 'Trial Balance';
    }
    
    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 45,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        $sheet->getStyle('A2:F2')->applyFromArray([
            'font' => [
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        // Table header styling
        $sheet->getStyle('A4:F4')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E5E5E5',
                ],
            ],
        ]);
        
        // Format number columns
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('C5:F' . $lastRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');
        
        // Grand total row styling
        $grandTotalRow = $lastRow;
        $isBalanced = $this->data['isBalanced'];
        
        $sheet->getStyle('A' . $grandTotalRow . ':F' . $grandTotalRow)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => $isBalanced ? 'D4EDDA' : 'F8D7DA',
                ],
            ],
        ]);
        
        return [];
    }
}