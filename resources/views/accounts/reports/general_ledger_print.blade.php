<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>General Ledger Report - Print</title>
    <style>
           @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            .no-print {
                display: none !important;
            }
            
            /* Prevent awkward breaks */
            .main-table {
                page-break-inside: auto;
            }
            
            .main-table thead {
                display: table-header-group;
            }
            
            .main-table tbody tr {
                page-break-inside: avoid;
            }
            
            .ledger-header {
                page-break-after: avoid;
            }
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h2 {
            margin: 0;
            font-size: 20px;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        
        .print-button {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .ledger-section {
            margin-bottom: 40px;
            /* page-break-inside: avoid; */
        }
        
        .ledger-header {
            background-color: #f5f5f5;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
        }
        
        .ledger-header h3 {
            margin: 0;
            font-size: 18px;
        }
        
        .info-table {
            width: 100%;
            margin-bottom: 10px;
        }
        
        .info-table td {
            padding: 5px;
            font-size: 13px;
        }
        
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .main-table th, .main-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .main-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 12px;
        }
        
        .main-table td {
            font-size: 12px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .opening-balance {
            background-color: #e8f5e9;
            font-weight: bold;
        }
        
        .closing-balance {
            background-color: #ffebee;
            font-weight: bold;
        }
        
        .narration {
            font-size: 11px;
            color: #666;
            font-style: italic;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
        
        .btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 14px;
            border-radius: 4px;
            margin: 5px;
        }
        
        .btn:hover {
            background-color: #0056b3;
        }
        
        /* .page-break {
            page-break-after: always;
        } */
                        .company-header {
    display: flex;
    align-items: flex-start;
    margin-bottom: 20px;
    border-bottom: 2px solid #ccc;
    padding-bottom: 10px;
  
    gap: 20px;
    flex-wrap: wrap;
    text-align: left;
}

.company-header .logo img {
    width: 120px;
    height: 80px;
}

.company-header .company-info {
    font-size: 13px;
    line-height: 1.6;
    max-width: 580px;
}

.company-header .company-info strong {
    font-size: 24px;
    color: #e16c2f;
}
    </style>
</head>
<body>
    <div class="print-button no-print">
        <button class="btn" onclick="window.print()">Print Report</button>
        <button class="btn" onclick="window.close()">Close</button>
    </div>
    
   <div class="company-header">
    <div class="logo">
        <img src="{{ asset('public/assets/logo.jpeg') }}" alt="RSK Logo">
    </div>
    <div class="company-info">
        <strong>RSK Canvas Trading</strong><br>
        No. 8 Lot 2921, Jalan PJS 3/1, Taman Medan, 46000 Petaling Jaya,<br>
        Selangor Darul Ehsan.<br>
        <span>Tel: +603-7781 7434 / +603-7785 7434</span><br>
        E-mail: sales@rsk.com.my
    </div>
</div>
    <div class="header">
        <h2>GENERAL LEDGER REPORT</h2>
        <p>From: {{ date('d-m-Y', strtotime($fromDate)) }} To: {{ date('d-m-Y', strtotime($toDate)) }}</p>
        @if($invoiceType !== 'all')
            <p>
                Filter: 
                @if($invoiceType == '1')
                    Sales Transactions Only
                @elseif($invoiceType == '2')
                    Purchase Transactions Only
                @elseif($invoiceType == 'manual')
                    Manual Entries Only
                @endif
            </p>
        @endif
        <p>Total Ledgers: {{ count($ledgerReports) }}</p>
    </div>
    
    @foreach($ledgerReports as $index => $report)
    <div class="ledger-section">
        <div class="ledger-header">
            <h3>{{ $report['ledger']->name }}</h3>
            <table class="info-table">
                <tr>
                    <td><strong>Ledger Code:</strong> {{ $report['ledger']->left_code }}/{{ $report['ledger']->right_code }}</td>
                    <td><strong>Group:</strong> {{ $report['ledger']->group->name ?? 'N/A' }}</td>
                    <td><strong>Type:</strong> {{ $report['ledger']->type == 1 ? 'Bank/Cash' : 'General' }}</td>
                    <td><strong>Report Date:</strong> {{ date('d-m-Y H:i:s') }}</td>
                </tr>
            </table>
        </div>
        
        <table class="main-table">
            <thead>
                <tr>
                    <th width="10%">Date</th>
                    <th width="15%">Voucher No</th>
                    <th width="12%">Type</th>
                    <th width="28%">Particulars</th>
                    <th width="12%" class="text-right">Debit</th>
                    <th width="12%" class="text-right">Credit</th>
                    <th width="11%" class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                <!-- Opening Balance -->
                <tr class="opening-balance">
                    <td colspan="4">Opening Balance</td>
                    <td class="text-right">{{ number_format($report['openingBalance']['debit'], 2) }}</td>
                    <td class="text-right">{{ number_format($report['openingBalance']['credit'], 2) }}</td>
                    <td class="text-right">
                        @php
                            $openingNet = $report['openingBalance']['debit'] - $report['openingBalance']['credit'];
                        @endphp
                        {{ $openingNet >= 0 ? number_format(abs($openingNet), 2) : '(' . number_format(abs($openingNet), 2) . ')' }}
                    </td>
                </tr>
                
                <!-- Transactions -->
             @if(isset($report['transactions']) && count($report['transactions']) > 0)
                @foreach($report['transactions'] as $transaction)
                <tr>
                    <td>{{ date('d-m-Y', strtotime($transaction->entry->date)) }}</td>
                    <td>{{ $transaction->entry->entry_code }}</td>
                    <td>
                        {{ @$transaction->entry->entry_type_name }}
                        @if($transaction->entry->inv_type)
                            <br><small>({{ $transaction->entry->inv_type == 1 ? 'Sales' : 'Purchase' }})</small>
                        @endif
                    </td>
                    <td>
                        @php
                            $oppositeEntries = $transaction->entry->entryItems
                                ->where('id', '!=', $transaction->id)
                                ->where('dc', '!=', $transaction->dc);
                            $particulars = [];
                            foreach($oppositeEntries as $opposite) {
                                $particulars[] = @$opposite->ledger->name;
                            }
                        @endphp
                        {{ implode(', ', $particulars) }}
                        @if($transaction->entry->narration)
                            <br><span class="narration">{{ $transaction->entry->narration }}</span>
                        @endif
                    </td>
                    <td class="text-right">
                        {{ $transaction->dc == 'D' ? number_format($transaction->amount, 2) : '-' }}
                    </td>
                    <td class="text-right">
                        {{ $transaction->dc == 'C' ? number_format($transaction->amount, 2) : '-' }}
                    </td>
                    <td class="text-right">
                        {{ $transaction->balance_type == 'Dr' ? number_format($transaction->running_balance, 2) : '(' . number_format($transaction->running_balance, 2) . ')' }}
                    </td>
                </tr>
                @endforeach
              
                @endif
                <!-- Closing Balance -->
                <tr class="closing-balance">
                    <td colspan="4">Closing Balance</td>
                    <td class="text-right">{{ number_format($report['closingBalance']['debit'], 2) }}</td>
                    <td class="text-right">{{ number_format($report['closingBalance']['credit'], 2) }}</td>
                    <td class="text-right">
                        @php
                            $closingNet = $report['closingBalance']['debit'] - $report['closingBalance']['credit'];
                        @endphp
                        {{ $closingNet >= 0 ? number_format(abs($closingNet), 2) : '(' . number_format(abs($closingNet), 2) . ')' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    @endforeach
    
    <div class="footer">
        <p>This is a computer-generated report and does not require signature.</p>
        <p>Generated on: {{ date('d-m-Y H:i:s') }}</p>
    </div>
    
    <script>
        // Auto-print on load
        window.onload = function() {
            // window.print();
        }
    </script>
</body>
</html>