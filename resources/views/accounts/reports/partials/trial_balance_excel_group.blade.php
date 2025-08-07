{{-- Display child groups --}}
@foreach($group['children'] as $childGroup)
<tr>
    <td>{{ str_repeat('  ', $childGroup['level']) }}{{ $childGroup['code'] }}</td>
    <td>{{ str_repeat('  ', $childGroup['level']) }}<strong>{{ $childGroup['name'] }}</strong></td>
    <td>0</td>
    <td>0</td>
    <td>{{ $childGroup['totalClosingDebit'] }}</td>
    <td>{{ $childGroup['totalClosingCredit'] }}</td>
</tr>

{{-- Recursively display child group's content --}}
@include('accounts.reports.partials.trial_balance_excel_group', [
    'group' => $childGroup, 
    'level' => $childGroup['level']
])
@endforeach

{{-- Display ledgers --}}
@foreach($group['ledgers'] as $ledger)
<tr>
    <td>{{ str_repeat('  ', $level + 1) }}{{ $ledger['code'] }}</td>
    <td>{{ str_repeat('  ', $level + 1) }}{{ $ledger['name'] }}</td>
    <td>{{ $ledger['openingDebit'] }}</td>
    <td>{{ $ledger['openingCredit'] }}</td>
    <td>{{ $ledger['closingDebit'] }}</td>
    <td>{{ $ledger['closingCredit'] }}</td>
</tr>
@endforeach