<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trial Balance Report - Print</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 20px;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
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
        
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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
        
        .group-header {
            background-color: #e8e8e8;
            font-weight: bold;
            font-size: 13px;
        }
        
        .group-row {
            background-color: #f8f8f8;
            font-weight: 600;
        }
        
        .ledger-row {
            font-weight: normal;
        }
        
        .indent-1 { padding-left: 30px; }
        .indent-2 { padding-left: 60px; }
        .indent-3 { padding-left: 90px; }
        
        .grand-total {
            font-weight: bold;
            font-size: 13px;
        }
        
        .balanced {
            background-color: #d4edda;
        }
        
        .unbalanced {
            background-color: #f8d7da;
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
        <h2>TRIAL BALANCE</h2>
        <p>As on: {{ date('d-m-Y', strtotime($toDate)) }}</p>
        <p>Period: {{ date('d-m-Y', strtotime($fromDate)) }} to {{ date('d-m-Y', strtotime($toDate)) }}</p>
    </div>
    
    <table class="main-table">
        <thead>
            <tr>
                <th width="10%">Account Code</th>
                <th width="40%">Account Name</th>
                <th width="12.5%" class="text-right">Opening Debit</th>
                <th width="12.5%" class="text-right">Opening Credit</th>
                <th width="12.5%" class="text-right">Closing Debit</th>
                <th width="12.5%" class="text-right">Closing Credit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trialBalanceData as $parentGroup)
            <tr class="group-header">
                <td>{{ $parentGroup['code'] }}</td>
                <td><strong>{{ $parentGroup['name'] }}</strong></td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right"><strong>{{ number_format($parentGroup['totalClosingDebit'], 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($parentGroup['totalClosingCredit'], 2) }}</strong></td>
            </tr>
            
            @include('accounts.reports.partials.trial_balance_print_group', [
                'group' => $parentGroup, 
                'level' => 0
            ])
            @endforeach
            
            <tr class="grand-total {{ $isBalanced ? 'balanced' : 'unbalanced' }}">
                <td colspan="2" class="text-right">Grand Total</td>
                <td class="text-right">{{ number_format($grandTotalOpeningDebit, 2) }}</td>
                <td class="text-right">{{ number_format($grandTotalOpeningCredit, 2) }}</td>
                <td class="text-right">{{ number_format($grandTotalClosingDebit, 2) }}</td>
                <td class="text-right">{{ number_format($grandTotalClosingCredit, 2) }}</td>
            </tr>
        </tbody>
    </table>
    
    @if(!$isBalanced)
    <p style="color: red; text-align: center; margin-top: 20px;">
        <strong>Trial Balance is not balanced! Difference: {{ number_format(abs($grandTotalClosingDebit - $grandTotalClosingCredit), 2) }}</strong>
    </p>
    @endif
    
    <div class="footer">
        <p>This is a computer-generated report and does not require signature.</p>
        <p>Generated on: {{ date('d-m-Y H:i:s') }}</p>
    </div>
    
    <script>
        // Auto-print on load (optional - uncomment if needed)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>