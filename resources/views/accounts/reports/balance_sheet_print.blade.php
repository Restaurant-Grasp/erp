<!DOCTYPE html>
<html>
<head>
    <title>Balance Sheet - Print</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            @page { margin: 0.5in; }
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            line-height: 1.4;
            margin: 20px;
        }
        .header-info {
            text-align: center;
            margin-bottom: 30px;
        }
        .header-info h2 {
            margin: 10px 0;
            font-size: 18px;
        }
        .header-info p {
            margin: 5px 0;
            font-size: 12px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th, td { 
            padding: 8px; 
            text-align: left; 
            border: 1px solid #ddd; 
        }
        th { 
            background-color: #f5f5f5 !important; 
            font-weight: bold;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .indent-1 { padding-left: 20px; }
        .indent-2 { padding-left: 40px; }
        .indent-3 { padding-left: 60px; }
        .group-header {
            background-color: #f8f9fa;
            font-weight: bold;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .group-total { 
            background-color: #f8f9fa; 
            font-weight: bold;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .footer-total { 
            background-color: #e9ecef; 
            font-weight: bold;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .text-danger { color: red; }
        .btn-print {
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        .btn-print:hover {
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

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Print Balance Sheet</button>
        <button class="btn-print" onclick="window.close()" style="background-color: #6c757d;">Close</button>
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
    <div class="header-info">
        <h2>BALANCE SHEET</h2>
        <p><strong>As on: {{ date('d-m-Y', strtotime($asOnDate)) }}</strong></p>
        <p>Financial Year: {{ date('d-m-Y', strtotime($activeYear->from_year_month)) }} to {{ date('d-m-Y', strtotime($activeYear->to_year_month)) }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th width="60%">Account Name</th>
                <th width="20%" class="text-right">Current Year</th>
                <th width="20%" class="text-right">Previous Year</th>
            </tr>
        </thead>
        <tbody>
            @foreach($balanceSheetData as $parentGroup)
                {{-- Parent Group Header --}}
                <tr class="group-header">
                    <td colspan="3">
                        <strong>({{ $parentGroup['code'] }}) {{ strtoupper($parentGroup['name']) }}</strong>
                    </td>
                </tr>
                
                {{-- Render group contents --}}
                @include('accounts.reports.partials.balance_sheet_print_group', [
                    'group' => $parentGroup, 
                    'level' => 0
                ])
                
                {{-- Add Current P&L for Equity section --}}
                @if($parentGroup['code'] == '3000' && isset($parentGroup['profitLoss']))
                <tr>
                    <td class="indent-1">{{ $parentGroup['profitLoss']['name'] }}</td>
                    <td class="text-right">
                         @if($parentGroup['profitLoss']['current'] > 0)
							({{ number_format($parentGroup['profitLoss']['current'], 2) }})
						@else
							{{ number_format(abs($parentGroup['profitLoss']['current']), 2) }}
						@endif
                    </td>
                    <td class="text-right">-</td>
                </tr>
                @endif
                
                {{-- Group Total --}}
                <tr class="group-total">
                    <td class="text-right">
                        TOTAL {{ strtoupper($parentGroup['name']) }}
                    </td>
                    <td class="text-right">
                        @if($parentGroup['currentBalance'] < 0)
                            ({{ number_format(abs($parentGroup['currentBalance']), 2) }})
                        @else
                            {{ number_format($parentGroup['currentBalance'], 2) }}
                        @endif
                    </td>
                    <td class="text-right">
                        @if($parentGroup['previousBalance'] < 0)
                            ({{ number_format(abs($parentGroup['previousBalance']), 2) }})
                        @else
                            {{ number_format($parentGroup['previousBalance'], 2) }}
                        @endif
                    </td>
                </tr>
                
                {{-- Add spacing row between sections --}}
                <tr>
                    <td colspan="3" style="height: 10px; border: none;"></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="footer-total">
                <td>TOTAL LIABILITIES & EQUITY</td>
                <td class="text-right">
                    @php $totalLiabEquity = $totalLiabilities['current'] + $totalEquity['current']; @endphp
                    @if($totalLiabEquity < 0)
                        ({{ number_format(abs($totalLiabEquity), 2) }})
                    @else
                        {{ number_format($totalLiabEquity, 2) }}
                    @endif
                </td>
                <td class="text-right">
                    @php $totalLiabEquityPrev = $totalLiabilities['previous'] + $totalEquity['previous']; @endphp
                    @if($totalLiabEquityPrev < 0)
                        ({{ number_format(abs($totalLiabEquityPrev), 2) }})
                    @else
                        {{ number_format($totalLiabEquityPrev, 2) }}
                    @endif
                </td>
            </tr>
            
            @php
                // Balance check logic
                $leftSide = $totalAssets['current'];
                $rightSide = $totalLiabilities['current'] + $totalEquity['current'];
                $isBalanced = false;
                if ($leftSide > 0 && $rightSide < 0) {
                    $isBalanced = abs($leftSide - abs($rightSide)) < 0.01;
                } else if ($leftSide < 0 && $rightSide > 0) {
                    $isBalanced = abs(abs($leftSide) - $rightSide) < 0.01;
                } else {
                    $isBalanced = abs($leftSide - $rightSide) < 0.01;
                }
            @endphp
            
            @if(!$isBalanced)
            <tr>
                <td colspan="3" class="text-center text-danger">
                    <strong>Balance Sheet is not balanced! Difference: {{ number_format(abs($leftSide - $rightSide), 2) }}</strong>
                </td>
            </tr>
            @endif
        </tfoot>
    </table>
    
    <script>
        // Auto-print when page loads if opened for printing
        if (window.location.search.includes('autoprint=true')) {
            window.onload = function() {
                window.print();
            }
        }
    </script>
</body>
</html>