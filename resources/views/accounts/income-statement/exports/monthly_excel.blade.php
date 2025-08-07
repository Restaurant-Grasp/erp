<table>
    <thead>
               {{-- Company Address Section --}}
        <tr>
            <th colspan="{{ count($incomeStatementData['months']) + 2 }}" style="text-align: center; font-size: 14px; font-weight: bold;">
                RSK Canvas Trading
            </th>
        </tr>
        <tr>
            <th colspan="{{ count($incomeStatementData['months']) + 2 }}" style="text-align: center; font-size: 11px;">
                No. 8 Lot 2921, Jalan PJS 3/1, Taman Medan, 46000 Petaling Jaya, Selangor Darul Ehsan. Tel: +603-7781 7434 / +603-7785 7434, E-mail: sales@rsk.com.my
            </th>
        </tr>
        <tr>
            <th colspan="{{ count($incomeStatementData['months']) + 2 }}"></th>
        </tr>
        <tr>
            <th colspan="{{ count($incomeStatementData['months']) + 2 }}" 
                style="text-align: center; font-size: 16px; font-weight: bold;">
                INCOME STATEMENT
            </th>
        </tr>
        <tr>
            <th colspan="{{ count($incomeStatementData['months']) + 2 }}" style="text-align: center;">
                From {{ date('d-m-Y', strtotime($fromDate)) }} to {{ date('d-m-Y', strtotime($toDate)) }}
            </th>
        </tr>
        @if($selectedFund)
        <tr>
            <th colspan="{{ count($incomeStatementData['months']) + 2 }}" style="text-align: center;">
                Fund: {{ $selectedFund->code }} - {{ $selectedFund->name }}
            </th>
        </tr>
        @endif
        <tr>
            <th colspan="{{ count($incomeStatementData['months']) + 2 }}"></th>
        </tr>
        <tr>
            <th>Account Name</th>
            @foreach($incomeStatementData['months'] as $month)
            <th style="text-align: center;">{{ $month['month'] }}</th>
            @endforeach
            <th style="text-align: center;">Total</th>
        </tr>
    </thead>
    <tbody>
        {{-- Revenue Section --}}
        <tr>
            <td colspan="{{ count($incomeStatementData['months']) + 2 }}" style="font-weight: bold;">Revenue</td>
        </tr>
        @include('accounts.income-statement.exports.excel_monthly_group_rows', [
            'groupData' => $incomeStatementData['revenue'],
            'months' => $incomeStatementData['months']
        ])
        <tr style="font-weight: bold;">
            <td>Total Revenue</td>
            @foreach($incomeStatementData['months'] as $index => $month)
            <td style="text-align: right;">
                {{ number_format(abs($incomeStatementData['totalRevenue']['month_' . $index]), 2) }}
            </td>
            @endforeach
            <td style="text-align: right;">{{ number_format(abs($incomeStatementData['totalRevenue']['total']), 2) }}</td>
        </tr>
        
        {{-- Direct Cost Section --}}
        <tr>
            <td colspan="{{ count($incomeStatementData['months']) + 2 }}" style="font-weight: bold;">Direct Cost</td>
        </tr>
        @include('accounts.income-statement.exports.excel_monthly_group_rows', [
            'groupData' => $incomeStatementData['directCost'],
            'months' => $incomeStatementData['months']
        ])
        <tr style="font-weight: bold;">
            <td>Total Direct Cost</td>
            @foreach($incomeStatementData['months'] as $index => $month)
            <td style="text-align: right;">
                {{ number_format(abs($incomeStatementData['totalDirectCost']['month_' . $index]), 2) }}
            </td>
            @endforeach
            <td style="text-align: right;">{{ number_format(abs($incomeStatementData['totalDirectCost']['total']), 2) }}</td>
        </tr>
        
        {{-- Gross Surplus/Deficit --}}
        <tr style="font-weight: bold;">
            <td>Gross Surplus/Deficit</td>
            @foreach($incomeStatementData['months'] as $index => $month)
            <td style="text-align: right;">
                @if($incomeStatementData['grossSurplus']['month_' . $index] < 0)
                    ({{ number_format(abs($incomeStatementData['grossSurplus']['month_' . $index]), 2) }})
                @else
                    {{ number_format($incomeStatementData['grossSurplus']['month_' . $index], 2) }}
                @endif
            </td>
            @endforeach
            <td style="text-align: right;">
                @if($incomeStatementData['grossSurplus']['total'] < 0)
                    ({{ number_format(abs($incomeStatementData['grossSurplus']['total']), 2) }})
                @else
                    {{ number_format($incomeStatementData['grossSurplus']['total'], 2) }}
                @endif
            </td>
        </tr>
        
        {{-- Incomes Section --}}
        <tr>
            <td colspan="{{ count($incomeStatementData['months']) + 2 }}" style="font-weight: bold;">Incomes</td>
        </tr>
        @include('accounts.income-statement.exports.excel_monthly_group_rows', [
            'groupData' => $incomeStatementData['incomes'],
            'months' => $incomeStatementData['months']
        ])
        <tr style="font-weight: bold;">
            <td>Total Incomes</td>
            @foreach($incomeStatementData['months'] as $index => $month)
            <td style="text-align: right;">
                {{ number_format(abs($incomeStatementData['totalIncomes']['month_' . $index]), 2) }}
            </td>
            @endforeach
            <td style="text-align: right;">{{ number_format(abs($incomeStatementData['totalIncomes']['total']), 2) }}</td>
        </tr>
        
        {{-- Expenses Section --}}
        <tr>
            <td colspan="{{ count($incomeStatementData['months']) + 2 }}" style="font-weight: bold;">Expenses</td>
        </tr>
        @include('accounts.income-statement.exports.excel_monthly_group_rows', [
            'groupData' => $incomeStatementData['expenses'],
            'months' => $incomeStatementData['months']
        ])
        <tr style="font-weight: bold;">
            <td>Total Expenses</td>
            @foreach($incomeStatementData['months'] as $index => $month)
            <td style="text-align: right;">
                {{ number_format(abs($incomeStatementData['totalExpenses']['month_' . $index]), 2) }}
            </td>
            @endforeach
            <td style="text-align: right;">{{ number_format(abs($incomeStatementData['totalExpenses']['total']), 2) }}</td>
        </tr>
        
        {{-- Surplus/Deficit Before Taxation --}}
        <tr style="font-weight: bold;">
            <td>Surplus/Deficit Before Taxation</td>
            @foreach($incomeStatementData['months'] as $index => $month)
            <td style="text-align: right;">
                @if($incomeStatementData['surplusBeforeTax']['month_' . $index] < 0)
                    ({{ number_format(abs($incomeStatementData['surplusBeforeTax']['month_' . $index]), 2) }})
                @else
                    {{ number_format($incomeStatementData['surplusBeforeTax']['month_' . $index], 2) }}
                @endif
            </td>
            @endforeach
            <td style="text-align: right;">
                @if($incomeStatementData['surplusBeforeTax']['total'] < 0)
                    ({{ number_format(abs($incomeStatementData['surplusBeforeTax']['total']), 2) }})
                @else
                    {{ number_format($incomeStatementData['surplusBeforeTax']['total'], 2) }}
                @endif
            </td>
        </tr>
        
        {{-- Taxation Section --}}
        <tr>
            <td colspan="{{ count($incomeStatementData['months']) + 2 }}" style="font-weight: bold;">Taxation</td>
        </tr>
        @include('accounts.income-statement.exports.excel_monthly_group_rows', [
            'groupData' => $incomeStatementData['taxation'],
            'months' => $incomeStatementData['months']
        ])
        <tr style="font-weight: bold;">
            <td>Total Taxation</td>
            @foreach($incomeStatementData['months'] as $index => $month)
            <td style="text-align: right;">
                {{ number_format(abs($incomeStatementData['totalTaxation']['month_' . $index]), 2) }}
            </td>
            @endforeach
            <td style="text-align: right;">{{ number_format(abs($incomeStatementData['totalTaxation']['total']), 2) }}</td>
        </tr>
        
        <tr>
            <td colspan="{{ count($incomeStatementData['months']) + 2 }}"></td>
        </tr>
        
        <tr style="font-weight: bold;">
            <td>
                @if($incomeStatementData['surplusAfterTax']['total'] >= 0)
                    Total Profit Amount
                @else
                    Total Loss Amount
                @endif
            </td>
            @foreach($incomeStatementData['months'] as $index => $month)
            <td style="text-align: right;">
                @if($incomeStatementData['surplusAfterTax']['month_' . $index] < 0)
                    ({{ number_format(abs($incomeStatementData['surplusAfterTax']['month_' . $index]), 2) }})
                @else
                    {{ number_format(abs($incomeStatementData['surplusAfterTax']['month_' . $index]), 2) }}
                @endif
            </td>
            @endforeach
            <td style="text-align: right; font-size: 14px;">
                {{ number_format(abs($incomeStatementData['surplusAfterTax']['total']), 2) }}
            </td>
        </tr>
    </tbody>
</table>