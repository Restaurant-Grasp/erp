@if(isset($groupData['ledgers']))
    @foreach($groupData['ledgers'] as $ledger)
    <tr>
        <td class="indent-1">{{ $ledger['code'] }}{{ $ledger['name'] }}</td>
        @foreach($months as $index => $month)
        <td class="text-right">
            @if(($ledger['month_' . $index] ?? 0) < 0)
                ({{ number_format(abs($ledger['month_' . $index]), 2) }})
            @else
                {{ number_format(abs($ledger['month_' . $index] ?? 0), 2) }}
            @endif
        </td>
        @endforeach
        <td class="text-right">
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
        <td class="indent-1 bold">{{ $subGroup['name'] }}</td>
        @for($i = 0; $i <= count($months); $i++)
        <td></td>
        @endfor
    </tr>
    @include('accounts.income-statement.exports.pdf_monthly_group_rows', [
        'groupData' => $subGroup['data'],
        'months' => $months
    ])
    @endforeach
@endif