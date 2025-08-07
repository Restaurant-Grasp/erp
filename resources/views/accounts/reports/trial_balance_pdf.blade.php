<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trial Balance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
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
            font-size: 18px;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 12px;
            color: #666;
        }
        
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .main-table th, .main-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        
        .main-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 11px;
        }
        
        .main-table td {
            font-size: 10px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .group-header {
            background-color: #e8e8e8;
            font-weight: bold;
            font-size: 12px;
        }
        
        .group-row {
            background-color: #f8f8f8;
            font-weight: 600;
        }
        
        .ledger-row {
            font-weight: normal;
        }
        
        .indent-1 { padding-left: 20px; }
        .indent-2 { padding-left: 40px; }
        .indent-3 { padding-left: 60px; }
        
        .grand-total {
            font-weight: bold;
            font-size: 12px;
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
            font-size: 10px;
            color: #666;
        }
        
        @page {
            margin: 0.5in;
        }




    </style>
</head>
<body>
<table width="100%" style="border-bottom: 2px solid #ccc; margin-bottom: 20px; padding-bottom: 10px;">
    <tr>
        <td width="120" style="vertical-align: top;">
            <img src="{{ asset('public/assets/logo.jpeg') }}" width="120" height="80" alt="RSK Logo">
        </td>
        <td style="padding-left: 0px; font-size: 13px; line-height: 1.6;">
            <strong style="font-size: 24px; color: #e16c2f;">RSK Canvas Trading</strong><br>
            No. 8 Lot 2921, Jalan PJS 3/1, Taman Medan, 46000 Petaling Jaya,<br>
            Selangor Darul Ehsan.<br>
            <span>Tel: +603-7781 7434 / +603-7785 7434</span><br>
            E-mail: sales@rsk.com.my
        </td>
    </tr>
</table>


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
            
            @include('accounts.reports.partials.trial_balance_pdf_group', [
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
    <p style="color: red; text-align: center; margin-top: 10px;">
        <strong>Trial Balance is not balanced! Difference: {{ number_format(abs($grandTotalDebit - $grandTotalCredit), 2) }}</strong>
    </p>
    @endif
    
    <div class="footer">
        <p>Generated on: {{ date('d-m-Y H:i:s') }}</p>
    </div>
</body>
</html>