{{-- Display child groups --}}
@foreach($group['children'] as $childGroup)
<tr class="group-row">
    <td class="indent-{{ $childGroup['level'] }}">{{ $childGroup['code'] }}</td>
    <td class="indent-{{ $childGroup['level'] }}">
        <strong>{{ $childGroup['name'] }}</strong>
    </td>
    <td class="text-right">-</td>
    <td class="text-right">-</td>
    <td class="text-right">
        <strong>{{ number_format($childGroup['totalClosingDebit'], 2) }}</strong>
    </td>
    <td class="text-right">
        <strong>{{ number_format($childGroup['totalClosingCredit'], 2) }}</strong>
    </td>
</tr>

{{-- Recursively display child group's content --}}
@include('accounts.reports.partials.trial_balance_print_group', [
    'group' => $childGroup, 
    'level' => $childGroup['level']
])
@endforeach

{{-- Display ledgers --}}
@foreach($group['ledgers'] as $ledger)
<tr class="ledger-row">
    <td class="indent-{{ $level + 1 }}">{{ $ledger['code'] }}</td>
    <td class="indent-{{ $level + 1 }}">{{ $ledger['name'] }}</td>
    <td class="text-right">{{ number_format($ledger['openingDebit'], 2) }}</td>
    <td class="text-right">{{ number_format($ledger['openingCredit'], 2) }}</td>
    <td class="text-right">{{ number_format($ledger['closingDebit'], 2) }}</td>
    <td class="text-right">{{ number_format($ledger['closingCredit'], 2) }}</td>
</tr>
@endforeach