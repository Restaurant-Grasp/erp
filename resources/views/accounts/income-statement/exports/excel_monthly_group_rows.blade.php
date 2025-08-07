@if(isset($groupData['ledgers']))
    @foreach($groupData['ledgers'] as $ledger)
    <tr>
        <td style="padding-left: 20px;">{{ $ledger['code'] }}{{ $ledger['name'] }}</td>
        @foreach($months as $index => $month)
        <td style="text-align: right;">
            @if($ledger['month_' . $index] < 0)
                ({{ number_format(abs($ledger['month_' . $index]), 2) }})
            @else
                {{ number_format(abs($ledger['month_' . $index]), 2) }}
            @endif
        </td>
        @endforeach
        <td style="text-align: right;">
            @if($ledger['total'] < 0)
                ({{ number_format(abs($ledger['total']), 2) }})
            @else
                {{ number_format(abs($ledger['total']), 2) }}
            @endif
        </td>
    </tr>
    @endforeach
@endif

@if(isset($groupData['groups']))
    @foreach($groupData['groups'] as $subGroup)
    <tr>
        <td style="padding-left: 15px; font-weight: bold;">{{ $subGroup['name'] }}</td>
        @for($i = 0; $i <= count($months); $i++)
        <td></td>
        @endfor
    </tr>
    @include('accounts.income-statement.exports.excel_monthly_group_rows', [
        'groupData' => $subGroup['data'],
        'months' => $months
    ])
    @endforeach
@endif