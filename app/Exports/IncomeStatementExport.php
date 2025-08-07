<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class IncomeStatementExport implements FromView, WithTitle, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;
    
    private $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function view(): View
    {
        if ($this->data['displayType'] == 'monthly') {
            return view('accounts.income-statement.exports.monthly_excel', $this->data);
        } else {
            return view('accounts.income-statement.exports.full_excel', $this->data);
        }
    }
    
    public function title(): string
    {
        return 'Income Statement';
    }
        /**
     * Format column B as simple decimal numbers
     */
    public function columnFormats(): array
    {
    
         return [
        'B' => NumberFormat::FORMAT_NUMBER_00,
        'C' => NumberFormat::FORMAT_NUMBER_00,
        'D' => NumberFormat::FORMAT_NUMBER_00,
        'E' => NumberFormat::FORMAT_NUMBER_00,

    ];
    }
}