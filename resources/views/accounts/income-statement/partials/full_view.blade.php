<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr style="background-color: #f5f5f5;">
                <th width="70%">Account Name</th>
                <th width="30%" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            {{-- Revenue Section --}}
            <tr>
                <td colspan="2" style="background-color: #e9ecef; font-weight: bold;">Revenue</td>
            </tr>
            @include('accounts.income-statement.partials.group_rows', [
                'groupData' => $incomeStatementData['revenue'],
                'fromDate' => $fromDate,
                'toDate' => $toDate
            ])
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td class="text-right">Total Revenue</td>
                <td class="text-right">{{ number_format(abs($incomeStatementData['totalRevenue']), 2) }}</td>
            </tr>
            
            {{-- Direct Cost Section --}}
            <tr>
                <td colspan="2" style="background-color: #e9ecef; font-weight: bold;">Direct Cost</td>
            </tr>
            @include('accounts.income-statement.partials.group_rows', [
                'groupData' => $incomeStatementData['directCost'],
                'fromDate' => $fromDate,
                'toDate' => $toDate
            ])
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td class="text-right">Total Direct Cost</td>
                <td class="text-right">{{ number_format(abs($incomeStatementData['totalDirectCost']), 2) }}</td>
            </tr>
            
            {{-- Gross Surplus --}}
            <tr style="background-color: #f8f9fa; font-weight: bold;">
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
            <tr>
                <td colspan="2" style="background-color: #e9ecef; font-weight: bold;">Incomes</td>
            </tr>
            @include('accounts.income-statement.partials.group_rows', [
                'groupData' => $incomeStatementData['incomes'],
                'fromDate' => $fromDate,
                'toDate' => $toDate
            ])
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td class="text-right">Total Incomes</td>
                <td class="text-right">{{ number_format(abs($incomeStatementData['totalIncomes']), 2) }}</td>
            </tr>
            
            {{-- Expenses Section --}}
            <tr>
                <td colspan="2" style="background-color: #e9ecef; font-weight: bold;">Expenses</td>
            </tr>
            @include('accounts.income-statement.partials.group_rows', [
                'groupData' => $incomeStatementData['expenses'],
                'fromDate' => $fromDate,
                'toDate' => $toDate
            ])
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td class="text-right">Total Expenses</td>
                <td class="text-right">{{ number_format(abs($incomeStatementData['totalExpenses']), 2) }}</td>
            </tr>
            
            {{-- Surplus Before Taxation --}}
            <tr style="background-color: #f8f9fa; font-weight: bold;">
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
            <tr>
                <td colspan="2" style="background-color: #e9ecef; font-weight: bold;">Taxation</td>
            </tr>
            @include('accounts.income-statement.partials.group_rows', [
                'groupData' => $incomeStatementData['taxation'],
                'fromDate' => $fromDate,
                'toDate' => $toDate
            ])
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td class="text-right">Total Taxation</td>
                <td class="text-right">{{ number_format(abs($incomeStatementData['totalTaxation']), 2) }}</td>
            </tr>
            
            {{-- Surplus After Taxation --}}
            <tr style="background-color: #f8f9fa; font-weight: bold;">
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
            <tr style="background-color: #343a40; color: white;">
                <td colspan="2" class="text-center font-weight-bold" style="font-size: 18px;">
                    @if($incomeStatementData['surplusAfterTax'] >= 0)
                        Total Profit Amount is {{ number_format($incomeStatementData['surplusAfterTax'], 2) }}
                    @else
                        Total Loss Amount is {{ number_format(abs($incomeStatementData['surplusAfterTax']), 2) }}
                    @endif
                </td>
            </tr>
        </tfoot>
    </table>
</div>