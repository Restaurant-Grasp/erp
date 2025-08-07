<table>
    <thead>
        <tr>
            <th colspan="3" style="text-align: center; font-size: 16px; font-weight: bold;">BALANCE SHEET</th>
        </tr>
        <tr>
            <th colspan="3" style="text-align: center;">As on: {{ date('d-m-Y', strtotime($asOnDate)) }}</th>
        </tr>
        <tr>
            <th colspan="3" style="text-align: center;">Financial Year: {{ date('d-m-Y', strtotime($activeYear->from_year_month)) }} to {{ date('d-m-Y', strtotime($activeYear->to_year_month)) }}</th>
        </tr>
        <tr>
            <th colspan="3"></th>
        </tr>
        <tr style="background-color: #f5f5f5;">
            <th style="font-weight: bold;">Account Name</th>
            <th style="font-weight: bold; text-align: right;">Current Year</th>
            <th style="font-weight: bold; text-align: right;">Previous Year</th>
        </tr>
    </thead>
    <tbody>
        @foreach($balanceSheetData as $parentGroup)
            {{-- Parent Group Header --}}
            <tr style="background-color: #f8f9fa;">
                <td style="font-weight: bold;">({{ $parentGroup['code'] }}) {{ strtoupper($parentGroup['name']) }}</td>
                <td></td>
                <td></td>
            </tr>
            
            {{-- Render group contents --}}
            @include('accounts.reports.partials.balance_sheet_excel_group', [
                'group' => $parentGroup, 
                'level' => 0
            ])
            
            {{-- Add Current P&L for Equity section --}}
            @if($parentGroup['code'] == '3000' && isset($parentGroup['profitLoss']))
            <tr>
                <td style="padding-left: 20px;">{{ $parentGroup['profitLoss']['name'] }}</td>
                <td style="text-align: right;">
                     @if($parentGroup['profitLoss']['current'] > 0)
						({{ number_format($parentGroup['profitLoss']['current'], 2) }})
					@else
						{{ number_format(abs($parentGroup['profitLoss']['current']), 2) }}
					@endif
                </td>
                <td style="text-align: right;">-</td>
            </tr>
            @endif
            
            {{-- Group Total --}}
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td style="text-align: right;">TOTAL {{ strtoupper($parentGroup['name']) }}</td>
                <td style="text-align: right;">
                    @if($parentGroup['currentBalance'] < 0)
                        ({{ number_format(abs($parentGroup['currentBalance']), 2) }})
                    @else
                        {{ number_format($parentGroup['currentBalance'], 2) }}
                    @endif
                </td>
                <td style="text-align: right;">
                    @if($parentGroup['previousBalance'] < 0)
                        ({{ number_format(abs($parentGroup['previousBalance']), 2) }})
                    @else
                        {{ number_format($parentGroup['previousBalance'], 2) }}
                    @endif
                </td>
            </tr>
            
            {{-- Add empty row for spacing --}}
            <tr>
                <td colspan="3"></td>
            </tr>
        @endforeach
        
        {{-- Footer Total --}}
        <tr style="background-color: #e9ecef; font-weight: bold;">
            <td>TOTAL LIABILITIES & EQUITY</td>
            <td style="text-align: right;">
                @php $totalLiabEquity = $totalLiabilities['current'] + $totalEquity['current']; @endphp
                @if($totalLiabEquity < 0)
                    ({{ number_format(abs($totalLiabEquity), 2) }})
                @else
                    {{ number_format($totalLiabEquity, 2) }}
                @endif
            </td>
            <td style="text-align: right;">
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
            <td colspan="3" style="text-align: center; color: red; font-weight: bold;">
                Balance Sheet is not balanced! Difference: {{ number_format(abs($leftSide - $rightSide), 2) }}
            </td>
        </tr>
        @endif
    </tbody>
</table>