<table style="font-size: 10px;">
    <thead>
        <tr>
            <th>Account Name</th>
            @foreach($incomeStatementData['months'] as $month)
            <th class="text-center">{{ $month['month'] }}</th>
            @endforeach
            <th class="text-center">Total</th>
        </tr>
    </thead>
    <tbody>
        {{-- Revenue Section --}}
        <tr class="section-header">
            <td colspan="{{ count($incomeStatementData['months']) + 2 }}">Revenue</td>
        </tr>
        @include('accounts.income-statement.exports.pdf_monthly_group_rows', [
            'groupData' => $incomeStatementData['revenue'],
            'months' => $incomeStatementData['months']
        ])
        <tr class="group-total">
            <td>Total Revenue</td>
            @foreach($incomeStatementData['months'] as $index => $month)
            <td class="text-right">
                {{ number_format(abs($incomeStatementData['totalRevenue']['month_' . $index]), 2) }}
            </td>
            @endforeach
            <td class="text-right">{{ number_format(abs($incomeStatementData['totalRevenue']['total']), 2) }}</td>
        </tr>
        
        {{-- Direct Cost Section --}}
        <tr class="section-header">
            <td colspan="{{ count($incomeStatementData['months']) + 2 }}">Direct Cost</td>
        </tr>
        @include('accounts.income-statement.exports.pdf_monthly_group_rows', [
            'groupData' => $incomeStatementData['directCost'],
            'months' => $incomeStatementData['months']
        ])
        <tr class="group-total">
            <td>Total Direct Cost</td>
            @foreach($incomeStatementData['months'] as $index => $month)
            <td class="text-right">
                {{ number_format(abs($incomeStatementData['totalDirectCost']['month_' . $index]), 2) }}
            </td>
            @endforeach
            <td class="text-right">{{ number_format(abs($incomeStatementData['totalDirectCost']['total']), 2) }}</td>
        </tr>
        
        {{-- Gross Surplus/Deficit --}}
        <tr class="group-total">
            <td>Gross Surplus/Deficit</td>
            @foreach($incomeStatementData['months'] as $index => $month)
            <td class="text-right">
                @if($incomeStatementData['grossSurplus']['month_' . $index] < 0)
                    ({{ number_format(abs($incomeStatementData['grossSurplus']['month_' . $index]), 2) }})
                @else
                    {{ number_format($incomeStatementData['grossSurplus']['month_' . $index], 2) }}
                @endif
            </td>
            @endforeach
            <td class="text-right">
                @if($incomeStatementData['grossSurplus']['total'] < 0)
                    ({{ number_format(abs($incomeStatementData['grossSurplus']['total']), 2) }})
                @else
                    {{ number_format($incomeStatementData['grossSurplus']['total'], 2) }}
                @endif
            </td>
        </tr>
        
        {{-- Incomes Section --}}
        <tr class="section-header">
            <td colspan="{{ count($incomeStatementData['months']) + 2 }}">Incomes</td>
        </tr>
        @include('accounts.income-statement.exports.pdf_monthly_group_rows', [
            'groupData' => $incomeStatementData['incomes'],
            'months' => $incomeStatementData['months']
        ])
        <tr class="group-total">
            <td>Total Incomes</td>
            @foreach($incomeStatementData['months'] as $index => $month)
            <td class="text-right">
                {{ number_format(abs($incomeStatementData['totalIncomes']['month_' . $index]), 2) }}
            </td>
            @endforeach
            <td class="text-right">{{ number_format(abs($incomeStatementData['totalIncomes']['total']), 2) }}</td>
        </tr>
        
        {{-- Expenses Section --}}
        <tr class="section-header">
            <td colspan="{{ count($incomeStatementData['months']) + 2 }}">Expenses</td>
        </tr>
        @include('accounts.income-statement.exports.pdf_monthly_group_rows', [
            'groupData' => $incomeStatementData['expenses'],
            'months' => $incomeStatementData['months']
        ])
        <tr class="group-total">
            <td>Total Expenses</td>
            @foreach($incomeStatementData['months'] as $index => $month)
            <td class="text-right">
                {{ number_format(abs($incomeStatementData['totalExpenses']['month_' . $index]), 2) }}
            </td>
            @endforeach
            <td class="text-right">{{ number_format(abs($incomeStatementData['totalExpenses']['total']), 2) }}</td>
        </tr>
        
        {{-- Surplus/Deficit Before Taxation --}}
        <tr class="group-total">
            <td>Surplus/Deficit Before Taxation</td>
            @foreach($incomeStatementData['months'] as $index => $month)
            <td class="text-right">
                @if($incomeStatementData['surplusBeforeTax']['month_' . $index] < 0)
                    ({{ number_format(abs($incomeStatementData['surplusBeforeTax']['month_' . $index]), 2) }})
                @else
                    {{ number_format($incomeStatementData['surplusBeforeTax']['month_' . $index], 2) }}
                @endif
            </td>
            @endforeach
            <td class="text-right">
                @if($incomeStatementData['surplusBeforeTax']['total'] < 0)
                    ({{ number_format(abs($incomeStatementData['surplusBeforeTax']['total']), 2) }})
                @else
                    {{ number_format($incomeStatementData['surplusBeforeTax']['total'], 2) }}
                @endif
            </td>
        </tr>
        
        {{-- Taxation Section --}}
        <tr class="section-header">
            <td colspan="{{ count($incomeStatementData['months']) + 2 }}">Taxation</td>
        </tr>
        @include('accounts.income-statement.exports.pdf_monthly_group_rows', [
            'groupData' => $incomeStatementData['taxation'],
            'months' => $incomeStatementData['months']
        ])
        <tr class="group-total">
            <td>Total Taxation</td>
            @foreach($incomeStatementData['months'] as $index => $month)
            <td class="text-right">
                {{ number_format(abs($incomeStatementData['totalTaxation']['month_' . $index]), 2) }}
            </td>
            @endforeach
            <td class="text-right">{{ number_format(abs($incomeStatementData['totalTaxation']['total']), 2) }}</td>
        </tr>
    </tbody>
    <tfoot>
        <tr class="footer">
            <td class="bold">
                @if($incomeStatementData['surplusAfterTax']['total'] >= 0)
                    Total Profit Amount
                @else
                    Total Loss Amount
                @endif
            </td>
            @foreach($incomeStatementData['months'] as $index => $month)
            <td class="text-right bold">
                @if($incomeStatementData['surplusAfterTax']['month_' . $index] < 0)
                    ({{ number_format(abs($incomeStatementData['surplusAfterTax']['month_' . $index]), 2) }})
                @else
                    {{ number_format(abs($incomeStatementData['surplusAfterTax']['month_' . $index]), 2) }}
                @endif
            </td>
            @endforeach
            <td class="text-right bold">
                {{ number_format(abs($incomeStatementData['surplusAfterTax']['total']), 2) }}
            </td>
        </tr>
    </tfoot>
</table>