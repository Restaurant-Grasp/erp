<div class="table-responsive">
    <table class="table table-bordered table-sm">
        <thead>
            <tr style="background-color: #f5f5f5;">
                <th>Account Name</th>
                @foreach($incomeStatementData['months'] as $month)
                <th class="text-center">{{ $month['month'] }}</th>
                @endforeach
                <th class="text-center">Total</th>
            </tr>
        </thead>
        <tbody>
            {{-- Revenue Section --}}
            <tr>
                <td colspan="{{ count($incomeStatementData['months']) + 2 }}" 
                    style="background-color: #e9ecef; font-weight: bold;">Revenue</td>
            </tr>
            @include('accounts.income-statement.partials.monthly_group_rows', [
                'groupData' => $incomeStatementData['revenue'],
                'months' => $incomeStatementData['months'],
                'fromDate' => $fromDate,
                'toDate' => $toDate
            ])
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td>Total Revenue</td>
                @foreach($incomeStatementData['months'] as $index => $month)
                <td class="text-right">
                    {{ number_format(abs($incomeStatementData['totalRevenue']['month_' . $index]), 2) }}
                </td>
                @endforeach
                <td class="text-right">{{ number_format(abs($incomeStatementData['totalRevenue']['total']), 2) }}</td>
            </tr>
            
            {{-- Direct Cost Section --}}
            <tr>
                <td colspan="{{ count($incomeStatementData['months']) + 2 }}" 
                    style="background-color: #e9ecef; font-weight: bold;">Direct Cost</td>
            </tr>
            @include('accounts.income-statement.partials.monthly_group_rows', [
                'groupData' => $incomeStatementData['directCost'],
                'months' => $incomeStatementData['months'],
                'fromDate' => $fromDate,
                'toDate' => $toDate
            ])
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td>Total Direct Cost</td>
                @foreach($incomeStatementData['months'] as $index => $month)
                <td class="text-right">
                    {{ number_format(abs($incomeStatementData['totalDirectCost']['month_' . $index]), 2) }}
                </td>
                @endforeach
                <td class="text-right">{{ number_format(abs($incomeStatementData['totalDirectCost']['total']), 2) }}</td>
            </tr>
            
            {{-- Gross Surplus/Deficit --}}
            <tr style="background-color: #f8f9fa; font-weight: bold;">
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
            <tr>
                <td colspan="{{ count($incomeStatementData['months']) + 2 }}" 
                    style="background-color: #e9ecef; font-weight: bold;">Incomes</td>
            </tr>
            @include('accounts.income-statement.partials.monthly_group_rows', [
                'groupData' => $incomeStatementData['incomes'],
                'months' => $incomeStatementData['months'],
                'fromDate' => $fromDate,
                'toDate' => $toDate
            ])
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td>Total Incomes</td>
                @foreach($incomeStatementData['months'] as $index => $month)
                <td class="text-right">
                    {{ number_format(abs($incomeStatementData['totalIncomes']['month_' . $index]), 2) }}
                </td>
                @endforeach
                <td class="text-right">{{ number_format(abs($incomeStatementData['totalIncomes']['total']), 2) }}</td>
            </tr>
            
            {{-- Expenses Section --}}
            <tr>
                <td colspan="{{ count($incomeStatementData['months']) + 2 }}" 
                    style="background-color: #e9ecef; font-weight: bold;">Expenses</td>
            </tr>
            @include('accounts.income-statement.partials.monthly_group_rows', [
                'groupData' => $incomeStatementData['expenses'],
                'months' => $incomeStatementData['months'],
                'fromDate' => $fromDate,
                'toDate' => $toDate
            ])
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td>Total Expenses</td>
                @foreach($incomeStatementData['months'] as $index => $month)
                <td class="text-right">
                    {{ number_format(abs($incomeStatementData['totalExpenses']['month_' . $index]), 2) }}
                </td>
                @endforeach
                <td class="text-right">{{ number_format(abs($incomeStatementData['totalExpenses']['total']), 2) }}</td>
            </tr>
            
            {{-- Surplus/Deficit Before Taxation --}}
            <tr style="background-color: #f8f9fa; font-weight: bold;">
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
            <tr>
                <td colspan="{{ count($incomeStatementData['months']) + 2 }}" 
                    style="background-color: #e9ecef; font-weight: bold;">Taxation</td>
            </tr>
            @include('accounts.income-statement.partials.monthly_group_rows', [
                'groupData' => $incomeStatementData['taxation'],
                'months' => $incomeStatementData['months'],
                'fromDate' => $fromDate,
                'toDate' => $toDate
            ])
            <tr style="background-color: #f8f9fa; font-weight: bold;">
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
            <tr style="background-color: #343a40; color: white;">
                <td class="font-weight-bold">
                    @if($incomeStatementData['surplusAfterTax']['total'] >= 0)
                        Total Profit Amount
                    @else
                        Total Loss Amount
                    @endif
                </td>
                @foreach($incomeStatementData['months'] as $index => $month)
                <td class="text-right font-weight-bold">
                    @if($incomeStatementData['surplusAfterTax']['month_' . $index] < 0)
                        ({{ number_format(abs($incomeStatementData['surplusAfterTax']['month_' . $index]), 2) }})
                    @else
                        {{ number_format(abs($incomeStatementData['surplusAfterTax']['month_' . $index]), 2) }}
                    @endif
                </td>
                @endforeach
                <td class="text-right font-weight-bold" style="font-size: 16px;">
                    {{ number_format(abs($incomeStatementData['surplusAfterTax']['total']), 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>