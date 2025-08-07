@if(isset($groupData['ledgers']))
    @foreach($groupData['ledgers'] as $ledger)
    <tr>
        <td style="padding-left: 20px;">{{ $ledger['code'] }}{{ $ledger['name'] }}</td>
        <td style="text-align: right;">
            @if($ledger['balance'] < 0)
                ({{ number_format(abs($ledger['balance']), 2) }})
            @else
                {{ number_format($ledger['balance'], 2) }}
            @endif
        </td>
    </tr>
    @endforeach
@endif

@if(isset($groupData['groups']))
    @foreach($groupData['groups'] as $subGroup)
    <tr>
        <td style="padding-left: 15px; font-weight: bold;">{{ $subGroup['name'] }}</td>
        <td></td>
    </tr>
    @include('accounts.income-statement.exports.excel_group_rows', ['groupData' => $subGroup['data']])
    @endforeach
@endif