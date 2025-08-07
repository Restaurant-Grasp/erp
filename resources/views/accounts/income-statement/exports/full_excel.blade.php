<table>
    <thead>
             {{-- Company Address Section --}}
        <tr>
            <th colspan="2" style="text-align: center; font-size: 14px; font-weight: bold;">
                RSK Canvas Trading
            </th>
        </tr>
        <tr>
            <th colspan="2" style="text-align: center; font-size: 11px;">
                No. 8 Lot 2921, Jalan PJS 3/1, Taman Medan, 46000 Petaling Jaya, Selangor Darul Ehsan. Tel: +603-7781 7434 / +603-7785 7434, E-mail: sales@rsk.com.my
            </th>
        </tr>
        <tr>
            <th colspan="2"></th>
        </tr>
        
        <tr>
            <th colspan="2" style="text-align: center; font-size: 16px; font-weight: bold;">
                INCOME STATEMENT
            </th>
        </tr>
        <tr>
            <th colspan="2" style="text-align: center;">
                From {{ date('d-m-Y', strtotime($fromDate)) }} to {{ date('d-m-Y', strtotime($toDate)) }}
            </th>
        </tr>
        @if($selectedFund)
        <tr>
            <th colspan="2" style="text-align: center;">
                Fund: {{ $selectedFund->code }} - {{ $selectedFund->name }}
            </th>
        </tr>
        @endif
        <tr>
            <th colspan="2"></th>
        </tr>
        <tr>
            <th width="70%">Account Name</th>
            <th width="30%">Amount</th>
        </tr>
    </thead>
    <tbody>
        {{-- Revenue Section --}}
        <tr>
            <td colspan="2" style="font-weight: bold;">Revenue</td>
        </tr>
        @include('accounts.income-statement.exports.excel_group_rows', ['groupData' => $incomeStatementData['revenue']])
        <tr style="font-weight: bold;">
            <td style="text-align: right;">Total Revenue</td>
            <td style="text-align: right;">{{ number_format(abs($incomeStatementData['totalRevenue']), 2) }}</td>
        </tr>
        
        {{-- Direct Cost Section --}}
        <tr>
            <td colspan="2" style="font-weight: bold;">Direct Cost</td>
        </tr>
        @include('accounts.income-statement.exports.excel_group_rows', ['groupData' => $incomeStatementData['directCost']])
        <tr style="font-weight: bold;">
            <td style="text-align: right;">Total Direct Cost</td>
            <td style="text-align: right;">{{ number_format(abs($incomeStatementData['totalDirectCost']), 2) }}</td>
        </tr>
        
        {{-- Gross Surplus --}}
        <tr style="font-weight: bold;">
            <td style="text-align: right;">Gross Surplus</td>
            <td style="text-align: right;">
                @if($incomeStatementData['grossSurplus'] < 0)
                    ({{ number_format(abs($incomeStatementData['grossSurplus']), 2) }})
                @else
                    {{ number_format($incomeStatementData['grossSurplus'], 2) }}
                @endif
            </td>
        </tr>
        
        {{-- Incomes Section --}}
        <tr>
            <td colspan="2" style="font-weight: bold;">Incomes</td>
        </tr>
        @include('accounts.income-statement.exports.excel_group_rows', ['groupData' => $incomeStatementData['incomes']])
        <tr style="font-weight: bold;">
            <td style="text-align: right;">Total Incomes</td>
            <td style="text-align: right;">{{ number_format(abs($incomeStatementData['totalIncomes']), 2) }}</td>
        </tr>
        
        {{-- Expenses Section --}}
        <tr>
            <td colspan="2" style="font-weight: bold;">Expenses</td>
        </tr>
        @include('accounts.income-statement.exports.excel_group_rows', ['groupData' => $incomeStatementData['expenses']])
        <tr style="font-weight: bold;">
            <td style="text-align: right;">Total Expenses</td>
            <td style="text-align: right;">{{ number_format(abs($incomeStatementData['totalExpenses']), 2) }}</td>
        </tr>
        
        {{-- Surplus Before Taxation --}}
        <tr style="font-weight: bold;">
            <td style="text-align: right;">Surplus Before Taxation</td>
            <td style="text-align: right;">
                @if($incomeStatementData['surplusBeforeTax'] < 0)
                    ({{ number_format(abs($incomeStatementData['surplusBeforeTax']), 2) }})
                @else
                    {{ number_format($incomeStatementData['surplusBeforeTax'], 2) }}
                @endif
            </td>
        </tr>
        
        {{-- Taxation Section --}}
        <tr>
            <td colspan="2" style="font-weight: bold;">Taxation</td>
        </tr>
        @include('accounts.income-statement.exports.excel_group_rows', ['groupData' => $incomeStatementData['taxation']])
        <tr style="font-weight: bold;">
            <td style="text-align: right;">Total Taxation</td>
            <td style="text-align: right;">{{ number_format(abs($incomeStatementData['totalTaxation']), 2) }}</td>
        </tr>
        
        {{-- Surplus After Taxation --}}
        <tr style="font-weight: bold;">
            <td style="text-align: right;">Surplus After Taxation</td>
            <td style="text-align: right;">
                @if($incomeStatementData['surplusAfterTax'] < 0)
                    ({{ number_format(abs($incomeStatementData['surplusAfterTax']), 2) }})
                @else
                    {{ number_format($incomeStatementData['surplusAfterTax'], 2) }}
                @endif
            </td>
        </tr>
        
        <tr>
            <td colspan="2"></td>
        </tr>
        
        <tr style="font-weight: bold; font-size: 14px;">
            <td colspan="2" style="text-align: center;">
                @if($incomeStatementData['surplusAfterTax'] >= 0)
                    Total Profit Amount is {{ number_format($incomeStatementData['surplusAfterTax'], 2) }}
                @else
                    Total Loss Amount is {{ number_format(abs($incomeStatementData['surplusAfterTax']), 2) }}
                @endif
            </td>
        </tr>
    </tbody>
</table>