{{-- Display child groups --}}
@foreach($group['children'] as $childGroup)
<tr class="group-row">
    <td class="indent-{{ $childGroup['level'] }}">
        <i class="fas  me-1"></i>
        {{ $childGroup['code'] }}
    </td>
    <td class="indent-{{ $childGroup['level'] }}">
       {{ $childGroup['name'] }}
        <small class="text-muted ms-2">({{ count($childGroup['ledgers']) }} ledger{{ count($childGroup['ledgers']) != 1 ? 's' : '' }})</small>
    </td>
    <td class="text-end">
        <span class="text-muted">-</span>
    </td>
    <td class="text-end">
        <span class="text-muted">-</span>
    </td>
    <td class="text-end">
    {{ number_format($childGroup['totalClosingDebit'], 2) }}
    </td>
    <td class="text-end">
      {{ number_format($childGroup['totalClosingCredit'], 2) }}
    </td>
</tr>

{{-- Recursively display child group's content --}}
@include('accounts.reports.partials.trial_balance_group', [
    'group' => $childGroup, 
    'level' => $childGroup['level']
])
@endforeach

{{-- Display ledgers --}}
@foreach($group['ledgers'] as $ledger)
<tr class="ledger-row">
    <td class="indent-{{ $level + 1 }}">
        <i class="fas me-1"></i>
        {{ $ledger['code'] }}
    </td>
    <td class="indent-{{ $level + 1 }}">
        <a href="{{ route('accounts.reports.general-ledger') }}?ledger_ids[]={{ $ledger['id'] }}&from_date={{ $fromDate }}&to_date={{ $toDate }}&invoice_type=all"
           class="ledger-link"
           target="_blank"
           onclick="event.preventDefault(); openTrialBalanceLedgerReport({{ $ledger['id'] }}, '{{ addslashes($ledger['name']) }}');"
           title="Click to view {{ $ledger['name'] }} ledger details from {{ date('d M Y', strtotime($fromDate)) }} to {{ date('d M Y', strtotime($toDate)) }}">
            {{ $ledger['name'] }}
        </a>
  
    </td>
    <td class="text-end">
        @if($ledger['openingDebit'] > 0)
        <span class="fw-semibold">{{ number_format($ledger['openingDebit'], 2) }}</span>
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
    <td class="text-end">
        @if($ledger['openingCredit'] > 0)
        <span class="">{{ number_format($ledger['openingCredit'], 2) }}</span>
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
    <td class="text-end">
        @if($ledger['closingDebit'] > 0)
        <span class="">{{ number_format($ledger['closingDebit'], 2) }}</span>
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
    <td class="text-end">
        @if($ledger['closingCredit'] > 0)
        <span class="">{{ number_format($ledger['closingCredit'], 2) }}</span>
        @else
        <span class="text-muted">-</span>
        @endif
    </td>
</tr>
@endforeach