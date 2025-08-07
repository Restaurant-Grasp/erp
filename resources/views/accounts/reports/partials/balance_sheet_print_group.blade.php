{{-- Display child groups --}}
@foreach($group['children'] as $childGroup)
<tr>
    <td class="indent-{{ $childGroup['level'] }}">
        <strong>({{ $childGroup['code'] }}) {{ strtoupper($childGroup['name']) }}</strong>
    </td>
    <td class="text-right">
        @if($childGroup['currentBalance'] != 0)
            @if($childGroup['currentBalance'] < 0)
                ({{ number_format(abs($childGroup['currentBalance']), 2) }})
            @else
                {{ number_format($childGroup['currentBalance'], 2) }}
            @endif
        @else
            -
        @endif
    </td>
    <td class="text-right">
        @if($childGroup['previousBalance'] != 0)
            @if($childGroup['previousBalance'] < 0)
                ({{ number_format(abs($childGroup['previousBalance']), 2) }})
            @else
                {{ number_format($childGroup['previousBalance'], 2) }}
            @endif
        @else
            -
        @endif
    </td>
</tr>

{{-- Recursively display child group's content --}}
@include('accounts.reports.partials.balance_sheet_print_group', [
    'group' => $childGroup, 
    'level' => $childGroup['level']
])
@endforeach

{{-- Display ledgers --}}
@foreach($group['ledgers'] as $ledger)
<tr>
    <td class="indent-{{ $level + 1 }}">
        ({{ $ledger['code'] }}) {{ $ledger['name'] }}
    </td>
    <td class="text-right">
        @if($ledger['currentBalance'] < 0)
            ({{ number_format(abs($ledger['currentBalance']), 2) }})
        @else
            {{ number_format($ledger['currentBalance'], 2) }}
        @endif
    </td>
    <td class="text-right">
        @if($ledger['previousBalance'] < 0)
            ({{ number_format(abs($ledger['previousBalance']), 2) }})
        @else
            {{ number_format($ledger['previousBalance'], 2) }}
        @endif
    </td>
</tr>
@endforeach