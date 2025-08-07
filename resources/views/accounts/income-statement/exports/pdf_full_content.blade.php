<table>
    <thead>
        <tr>
            <th width="70%">Account Name</th>
            <th width="30%" class="text-right">Amount</th>
        </tr>
    </thead>
    <tbody>
        {{-- Revenue Section --}}
        <tr class="section-header">
            <td colspan="2">Revenue</td>
        </tr>
        @include('accounts.income-statement.exports.pdf_group_rows', ['groupData' => $incomeStatementData['revenue']])
        <tr class="group-total">
            <td class="text-right">Total Revenue</td>
            <td class="text-right">{{ number_format(abs($incomeStatementData['totalRevenue']), 2) }}</td>
        </tr>
        
        {{-- Direct Cost Section --}}
        <tr class="section-header">
            <td colspan="2">Direct Cost</td>
        </tr>
        @include('accounts.income-statement.exports.pdf_group_rows', ['groupData' => $incomeStatementData['directCost']])
        <tr class="group-total">
            <td class="text-right">Total Direct Cost</td>
            <td class="text-right">{{ number_format(abs($incomeStatementData['totalDirectCost']), 2) }}</td>
        </tr>
        
        {{-- Gross Surplus --}}
        <tr class="group-total">
            <td class="text-right">Gross Surplus</td>
            <td class="text-right">
                @if($incomeStatementData['grossSurplus'] < 0)
                    ({{ number_format(abs($incomeStatementData['grossSurplus']), 2) }})
                @else
                    {{ number_format($incomeStatementData['grossSurplus'], 2) }}
                @endif
            </td>
        </tr>
        
        {{-- Incomes Section --}}
        <tr class="section-header">
            <td colspan="2">Incomes</td>
        </tr>
        @include('accounts.income-statement.exports.pdf_group_rows', ['groupData' => $incomeStatementData['incomes']])
        <tr class="group-total">
            <td class="text-right">Total Incomes</td>
            <td class="text-right">{{ number_format(abs($incomeStatementData['totalIncomes']), 2) }}</td>
        </tr>
        
        {{-- Expenses Section --}}
        <tr class="section-header">
            <td colspan="2">Expenses</td>
        </tr>
        @include('accounts.income-statement.exports.pdf_group_rows', ['groupData' => $incomeStatementData['expenses']])
        <tr class="group-total">
            <td class="text-right">Total Expenses</td>
            <td class="text-right">{{ number_format(abs($incomeStatementData['totalExpenses']), 2) }}</td>
        </tr>
        
        {{-- Surplus Before Taxation --}}
        <tr class="group-total">
            <td class="text-right">Surplus Before Taxation</td>
            <td class="text-right">
                @if($incomeStatementData['surplusBeforeTax'] < 0)
                    ({{ number_format(abs($incomeStatementData['surplusBeforeTax']), 2) }})
                @else
                    {{ number_format($incomeStatementData['surplusBeforeTax'], 2) }}
                @endif
            </td>
        </tr>
        
        {{-- Taxation Section --}}
        <tr class="section-header">
            <td colspan="2">Taxation</td>
        </tr>
        @include('accounts.income-statement.exports.pdf_group_rows', ['groupData' => $incomeStatementData['taxation']])
        <tr class="group-total">
            <td class="text-right">Total Taxation</td>
            <td class="text-right">{{ number_format(abs($incomeStatementData['totalTaxation']), 2) }}</td>
        </tr>
        
        {{-- Surplus After Taxation --}}
        <tr class="group-total">
            <td class="text-right">Surplus After Taxation</td>
            <td class="text-right">
                @if($incomeStatementData['surplusAfterTax'] < 0)
                    ({{ number_format(abs($incomeStatementData['surplusAfterTax']), 2) }})
                @else
                    {{ number_format($incomeStatementData['surplusAfterTax'], 2) }}
                @endif
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr class="footer">
            <td colspan="2">
                @if($incomeStatementData['surplusAfterTax'] >= 0)
                    Total Profit Amount is {{ number_format($incomeStatementData['surplusAfterTax'], 2) }}
                @else
                    Total Loss Amount is {{ number_format(abs($incomeStatementData['surplusAfterTax']), 2) }}
                @endif
            </td>
        </tr>
    </tfoot>
</table>