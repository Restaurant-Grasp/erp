{{-- Display child groups --}}
@if(!empty($group['children']))
    @foreach($group['children'] as $childGroup)
    <tr class="group-row">
        <td class="indent-{{ $childGroup['level'] }}">
            <span class="group-name">({{ $childGroup['code'] }}) {{ strtoupper($childGroup['name']) }}</span>
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
    @include('accounts.reports.partials.balance_sheet_group', [
        'group' => $childGroup, 
        'level' => $childGroup['level'],
        'asOnDate' => $asOnDate ?? date('Y-m-d'),
        'activeYear' => $activeYear
    ])
    @endforeach
@endif

{{-- Display ledgers with clickable links --}}
@if(!empty($group['ledgers']))
    @foreach($group['ledgers'] as $ledger)
    <tr class="ledger-row">
        <td class="indent-{{ $level + 1 }}">
            @php
    $ledgerId = $ledger['id'];
    $ledgerName = addslashes($ledger['name']);
    $fromDate = $activeYear->from_year_month ?? '';
    $toDate = $asOnDate ?? date('Y-m-d');
    $ledgerUrl = route('accounts.reports.general-ledger') . 
        '?ledger_ids[]=' . urlencode($ledgerId) .
        '&from_date=' . urlencode($fromDate) .
        '&to_date=' . urlencode($toDate) .
        '&invoice_type=all';
@endphp
           
<a href="{{ $ledgerUrl }}" 
   target="_blank"
   title="Click to view {{ $ledger['name'] }} ledger details from {{ $fromDate }} to {{ $toDate }}" class="ledger-link">
    ({{ $ledger['code'] }}) {{ $ledger['name'] }}
</a>
            @if($ledger['isPaLedger'] ?? false)
                <span class="badge badge-info badge-sm ml-1" title="Profit & Account Ledger">P&A</span>
            @endif
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
@endif