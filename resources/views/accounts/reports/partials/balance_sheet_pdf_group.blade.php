<!-- Simplified version of balance_sheet_group.blade.php for PDF -->
<tr>
    <td class="indent-{{ $level }}">
        <strong>({{ $group['code'] }}) {{ strtoupper($group['name']) }}</strong>
    </td>
    <td></td>
    <td></td>
</tr>

@foreach($group['children'] as $child)
    @include('accounts.reports.partials.balance_sheet_pdf_group', ['group' => $child, 'level' => $level + 1])
@endforeach

@foreach($group['ledgers'] as $ledger)
<tr>
    <td class="indent-{{ $level + 1 }}">({{ $ledger['code'] }}) {{ $ledger['name'] }}</td>
    <td class="text-right">
        {{ $ledger['currentBalance'] < 0 ? '(' . number_format(abs($ledger['currentBalance']), 2) . ')' : number_format($ledger['currentBalance'], 2) }}
    </td>
    <td class="text-right">
        {{ $ledger['previousBalance'] < 0 ? '(' . number_format(abs($ledger['previousBalance']), 2) . ')' : number_format($ledger['previousBalance'], 2) }}
    </td>
</tr>
@endforeach

@if($group['code'] == '3000' && isset($group['profitLoss']))
<tr>
    <td class="indent-1">{{ $group['profitLoss']['name'] }}</td>
    <td class="text-right">
        {{ $group['profitLoss']['current'] > 0 ? '(' . number_format($group['profitLoss']['current'], 2) . ')' : number_format(abs($group['profitLoss']['current']), 2) }}
    </td>
    <td class="text-right">-</td>
</tr>
@endif

<tr class="group-total">
    <td class="text-right">TOTAL {{ strtoupper($group['name']) }}</td>
    <td class="text-right">
        {{ $group['currentBalance'] < 0 ? '(' . number_format(abs($group['currentBalance']), 2) . ')' : number_format($group['currentBalance'], 2) }}
    </td>
    <td class="text-right">
        {{ $group['previousBalance'] < 0 ? '(' . number_format(abs($group['previousBalance']), 2) . ')' : number_format($group['previousBalance'], 2) }}
    </td>
</tr>