<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BalanceSheetExport implements FromArray, WithStyles, WithColumnWidths, WithTitle, WithEvents, WithColumnFormatting
{
    protected $data;
    protected $asOnDate;
    protected $activeYear;
    protected $rows = [];

    public function __construct($data)
    {
        $this->data = $data['balanceSheetData'];
        $this->asOnDate = $data['asOnDate'];
        $this->activeYear = $data['activeYear'];
        $this->totalAssets = $data['totalAssets'];
        $this->totalLiabilities = $data['totalLiabilities'];
        $this->totalEquity = $data['totalEquity'];
    }

    public function array(): array
    {
        // Build the data array
        $this->rows = [];
        
        // Company address header - all in one line
        $this->rows[] = ['RSK Canvas Trading', '', ''];
        $this->rows[] = ['No. 8 Lot 2921, Jalan PJS 3/1, Taman Medan, 46000 Petaling Jaya, Selangor Darul Ehsan. Tel: +603-7781 7434 / +603-7785 7434, E-mail: sales@rsk.com.my', '', ''];
        $this->rows[] = ['', '', '']; // Empty row
        
        // Add report header rows
        $this->rows[] = ['BALANCE SHEET', '', ''];
        $this->rows[] = ['As on: ' . date('d-m-Y', strtotime($this->asOnDate)), '', ''];
        $this->rows[] = ['Financial Year: ' . date('d-m-Y', strtotime($this->activeYear->from_year_month)) . ' to ' . date('d-m-Y', strtotime($this->activeYear->to_year_month)), '', ''];
        $this->rows[] = ['', '', '']; // Empty row
        
        // Add Account Name header row AFTER Balance Sheet heading
        $this->rows[] = ['Account Name', 'Current Year', 'Previous Year']; // Row 8 - Bold headers

        // Process each parent group
        foreach ($this->data as $parentGroup) {
            // Add parent group header
            $this->rows[] = [
                '(' . $parentGroup['code'] . ') ' . strtoupper($parentGroup['name']),
                '',
                ''
            ];

            // Process group recursively
            $this->processGroup($parentGroup, 0);

            // Add Current P&L for Equity section
            if ($parentGroup['code'] == '3000' && isset($parentGroup['profitLoss'])) {
                $this->rows[] = [
                    '    ' . $parentGroup['profitLoss']['name'],
                    (float) abs($parentGroup['profitLoss']['current']), // Raw number for Excel
                    '-' // Raw number instead of '-'
                ];
            }

            // Add group total
            $currentBalance = $parentGroup['currentBalance'] < 0
                ? '(' . number_format(abs($parentGroup['currentBalance']), 2) . ')'
                : number_format($parentGroup['currentBalance'], 2);
            $previousBalance = $parentGroup['previousBalance'] < 0
                ? '(' . number_format(abs($parentGroup['previousBalance']), 2) . ')'
                : number_format($parentGroup['previousBalance'], 2);

            $this->rows[] = [
                'TOTAL ' . strtoupper($parentGroup['name']),
                $currentBalance,
                $previousBalance
            ];

            // Add empty row for spacing
            $this->rows[] = ['', '', ''];
        }

        // Add footer total
        $totalLiabEquity = $this->totalLiabilities['current'] + $this->totalEquity['current'];
        $totalLiabEquityPrev = $this->totalLiabilities['previous'] + $this->totalEquity['previous'];

        $currentTotal = $totalLiabEquity < 0
            ? '(' . number_format(abs($totalLiabEquity), 2) . ')'
            : number_format($totalLiabEquity, 2);
        $previousTotal = $totalLiabEquityPrev < 0
            ? '(' . number_format(abs($totalLiabEquityPrev), 2) . ')'
            : number_format($totalLiabEquityPrev, 2);

        $this->rows[] = [
            'TOTAL LIABILITIES & EQUITY',
            $currentTotal,
            $previousTotal
        ];

        // Check if balanced
        $leftSide = $this->totalAssets['current'];
        $rightSide = $this->totalLiabilities['current'] + $this->totalEquity['current'];
        $isBalanced = false;

        if ($leftSide > 0 && $rightSide < 0) {
            $isBalanced = abs($leftSide - abs($rightSide)) < 0.01;
        } else if ($leftSide < 0 && $rightSide > 0) {
            $isBalanced = abs(abs($leftSide) - $rightSide) < 0.01;
        } else {
            $isBalanced = abs($leftSide - $rightSide) < 0.01;
        }

        if (!$isBalanced) {
            $this->rows[] = ['', '', ''];
            $this->rows[] = [
                'Balance Sheet is not balanced! Difference: ' . number_format(abs($leftSide - $rightSide), 2),
                '',
                ''
            ];
        }

        return $this->rows;
    }

    private function processGroup($group, $level)
    {
        $indent = str_repeat('    ', $level + 1); // 4 spaces per level

        // Process child groups
        foreach ($group['children'] as $childGroup) {
            $currentBalance = '';
            $previousBalance = '';

            if ($childGroup['currentBalance'] != 0) {
                $currentBalance = $childGroup['currentBalance'] < 0
                    ? '(' . number_format(abs($childGroup['currentBalance']), 2) . ')'
                    : number_format($childGroup['currentBalance'], 2);
            }

            if ($childGroup['previousBalance'] != 0) {
                $previousBalance = $childGroup['previousBalance'] < 0
                    ? '(' . number_format(abs($childGroup['previousBalance']), 2) . ')'
                    : number_format($childGroup['previousBalance'], 2);
            }

            $this->rows[] = [
                $indent . '(' . $childGroup['code'] . ') ' . strtoupper($childGroup['name']),
                $currentBalance ?: '-',
                $previousBalance ?: '-'
            ];

            // Recursively process child group
            $this->processGroup($childGroup, $childGroup['level']);
        }

        // Process ledgers
        $ledgerIndent = str_repeat('    ', $level + 2);
        foreach ($group['ledgers'] as $ledger) {
            $currentBalance = $ledger['currentBalance'] < 0
                ? '(' . number_format(abs($ledger['currentBalance']), 2) . ')'
                : number_format($ledger['currentBalance'], 2);
            $previousBalance = $ledger['previousBalance'] < 0
                ? '(' . number_format(abs($ledger['previousBalance']), 2) . ')'
                : number_format($ledger['previousBalance'], 2);

            $this->rows[] = [
                $ledgerIndent . '(' . $ledger['code'] . ') ' . $ledger['name'],
                $currentBalance,
                $previousBalance
            ];
        }
    }

    public function columnWidths(): array
    {
        return [
            'A' => 60,
            'B' => 20,
            'C' => 20,
        ];
    }

    public function title(): string
    {
        return 'Balance Sheet';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Company name styling (row 1)
            1 => [
                'font' => ['bold' => true, 'size' => 14]
            ],
            // Account Name header row styling (row 8)
            8 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F5F5F5']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Merge cells for company address header rows
                $sheet->mergeCells('A1:C1'); // Company name
                $sheet->mergeCells('A2:C2'); // Complete address in one line

                // Style and center align company name
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // CENTER ALIGN the full address line (row 2)
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Merge cells for report header rows
                $sheet->mergeCells('A4:C4'); // BALANCE SHEET
                $sheet->mergeCells('A5:C5'); // As on date
                $sheet->mergeCells('A6:C6'); // Financial year

                // Center align report header rows
                $sheet->getStyle('A4:C6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A5:A6')->getFont()->setSize(12);

                // Style Account Name header row (row 8) - BOLD with background and CENTER ALIGNED
                $sheet->getStyle('A8:C8')->getFont()->setBold(true);
                $sheet->getStyle('A8:C8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A8:C8')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F5F5F5');

                // Right align number columns (starting from row 9 after Account Name headers)
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('B9:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Bold for group headers and totals
                foreach ($this->rows as $index => $row) {
                    $rowNum = $index + 1;
                    if (
                        strpos($row[0], 'TOTAL') !== false ||
                        (isset($row[0]) && preg_match('/^\([0-9]+\)/', $row[0]))
                    ) {
                        $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true);
                        if (strpos($row[0], 'TOTAL') !== false) {
                            $sheet->getStyle('A' . $rowNum . ':C' . $rowNum)->getFont()->setBold(true);
                            $sheet->getStyle('A' . $rowNum . ':C' . $rowNum)->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('F8F9FA');
                        }
                    }
                }

                // Style the final total row
                $lastDataRow = 0;
                foreach ($this->rows as $index => $row) {
                    if ($row[0] == 'TOTAL LIABILITIES & EQUITY') {
                        $lastDataRow = $index + 1;
                        break;
                    }
                }

                if ($lastDataRow > 0) {
                    $sheet->getStyle('A' . $lastDataRow . ':C' . $lastDataRow)->getFont()->setBold(true);
                    $sheet->getStyle('A' . $lastDataRow . ':C' . $lastDataRow)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('E9ECEF');
                }

                // Add borders to data area (starting from Account Name headers - row 8)
                $sheet->getStyle('A8:C' . $highestRow)->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            },
        ];
    }

    /**
     * Format columns B and C as numbers with 2 decimal places
     */
    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2, // #,##0.00
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2, // #,##0.00
        ];
    }
}