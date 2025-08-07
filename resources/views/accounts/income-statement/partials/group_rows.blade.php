@if(isset($groupData['ledgers']))
    @foreach($groupData['ledgers'] as $ledger)
    <tr>
        <td class="pl-4">
            @php
    $ledgerId = $ledger['id'];
    $ledgerName = addslashes($ledger['name']);
    $fromDate = $fromDate ?? '';
    $toDate = $toDate ?? '';
    $fundId = request('fund_id', 'all'); // fallback to 'all' if not selected

    $ledgerUrl = route('accounts.reports.general-ledger') .
        '?ledger_ids[]=' . urlencode($ledgerId) .
        '&from_date=' . urlencode($fromDate) .
        '&to_date=' . urlencode($toDate) .
        '&invoice_type=all';

    if ($fundId !== 'all') {
        $ledgerUrl .= '&fund_id=' . urlencode($fundId);
    }
@endphp

<a href="{{ $ledgerUrl }}"
   target="_blank"
   title="Click to view {{ $ledger['name'] }} ledger details from {{ $fromDate }} to {{ $toDate }}" class="ledger-link">
    {{ $ledger['code'] }} {{ $ledger['name'] }}
</a>

        </td>
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
    <tr style="background-color: #f8f9fa;">
        <td class="pl-3"><strong>{{ $subGroup['name'] }}</strong></td>
        <td></td>
    </tr>
    @include('accounts.income-statement.partials.group_rows', [
        'groupData' => $subGroup['data'],
        'fromDate' => $fromDate ?? '',
        'toDate' => $toDate ?? ''
    ])
    @endforeach
@endif