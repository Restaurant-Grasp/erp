@if(isset($groupData['ledgers']))
    @foreach($groupData['ledgers'] as $ledger)
    <tr>
        <td class="indent-1">{{ $ledger['code'] }}{{ $ledger['name'] }}</td>
        <td class="text-right">
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
        <td class="indent-1 bold">{{ $subGroup['name'] }}</td>
        <td></td>
    </tr>
    @include('accounts.income-statement.exports.pdf_group_rows', ['groupData' => $subGroup['data']])
    @endforeach
@endif