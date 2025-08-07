@extends('layouts.app')
@section('title', 'Payment Voucher Details')
@section('content')

<div class="row">
    <div class="col-12">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('accounts.payment.list') }}">Payment List</a></li>
                <li class="breadcrumb-item active">Payment Details</li>
            </ol>
        </nav>

        <!-- Header Card -->
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">
                            <i class="fas fa-receipt text-primary me-2"></i>
                            Payment Voucher - <code class="text-primary">{{ $entry->entry_code }}</code>
                        </h5>
                        <small class="text-muted">Created on {{ $entry->created_at->format('d M Y, H:i') }}</small>
                    </div>
                    <div class="btn-group">
                        @php
                        $role = auth()->user()->getRoleNames()->first();
                        $permissions = getCurrentRolePermissions($role);
                        @endphp
                        
                        <a href="{{ route('accounts.payment.print', $entry->id) }}" 
                           class="btn btn-warning btn-sm" target="_blank">
                            <i class="fas fa-print me-1"></i>Print
                        </a>
                        
                        @if(empty($entry->inv_type))
                            @if ($permissions->contains('name', 'accounts.payment.edit'))
                            <a href="{{ route('accounts.payment.edit', $entry->id) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit me-1"></i>Edit
                            </a>
                            @endif
                            
                            @if ($permissions->contains('name', 'accounts.payment.create'))
                            <a href="{{ route('accounts.payment.copy', $entry->id) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-copy me-1"></i>Copy
                            </a>
                            @endif
                        @endif
                        
                        <a href="{{ route('accounts.payment.list') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-primary h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                        <h6 class="text-primary">Payment Date</h6>
                        <h5 class="mb-0">{{ $entry->date->format('d M Y') }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success h-100">
                    <div class="card-body text-center">
                        @php
                            $badgeClass = match($entry->payment) {
                                'CASH' => ['bg-success', 'fas fa-money-bill-wave'],
                                'CHEQUE' => ['bg-warning', 'fas fa-money-check'],
                                'ONLINE' => ['bg-info', 'fas fa-globe'],
                                default => ['bg-secondary', 'fas fa-credit-card']
                            };
                        @endphp
                        <i class="{{ $badgeClass[1] }} fa-2x {{ str_replace('bg-', 'text-', $badgeClass[0]) }} mb-2"></i>
                        <h6 class="{{ str_replace('bg-', 'text-', $badgeClass[0]) }}">Payment Mode</h6>
                        <span class="badge {{ $badgeClass[0] }} fs-6">{{ $entry->payment }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-building fa-2x text-info mb-2"></i>
                        <h6 class="text-info">Fund</h6>
                        <h5 class="mb-0">{{ $entry->fund->name }}</h5>
                        @if($entry->fund->code)
                            <small class="text-muted">({{ $entry->fund->code }})</small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-danger h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-dollar-sign fa-2x text-danger mb-2"></i>
                        <h6 class="text-danger">Total Amount</h6>
                        @php
                            $dr_total = $entry->dr_total;
                            if($discountItem && !empty($discountItem->amount)) $dr_total -= $discountItem->amount;
                        @endphp
                        <h4 class="mb-0 text-danger">RM {{ number_format($dr_total, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Payment Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="fw-medium" width="150">Paid To:</td>
                                <td>{{ $entry->paid_to }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Bank/Cash Account:</td>
                                <td>
                                    {{ $creditAccount->ledger->name }}
                                    <code class="text-muted">({{ $creditAccount->ledger->left_code }}/{{ $creditAccount->ledger->right_code }})</code>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="fw-medium" width="150">Created By:</td>
                                <td>{{ $entry->creator->name ?? 'System' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Last Updated:</td>
                                <td>{{ $entry->updated_at->format('d M Y, H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cheque/Online Details (if applicable) -->
        @if($entry->payment == 'CHEQUE' && $entry->cheque_no)
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="fas fa-money-check me-2"></i>Cheque Details</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Cheque Number:</strong><br>
                        <span class="badge bg-light text-dark fs-6">{{ $entry->cheque_no }}</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Cheque Date:</strong><br>
                        {{ $entry->cheque_date ? $entry->cheque_date->format('d M Y') : 'N/A' }}
                    </div>
                    @if($entry->collection_date)
                    <div class="col-md-4">
                        <strong>Collection Date:</strong><br>
                        {{ $entry->collection_date->format('d M Y') }}
                    </div>
                    @endif
                </div>
                
                @if($entry->return_date)
                <div class="alert alert-danger mt-3 mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Cheque Returned:</strong> {{ $entry->return_date->format('d M Y') }}
                    @if($entry->extra_charge > 0)
                        | <strong>Bank Charges:</strong> RM {{ number_format($entry->extra_charge, 2) }}
                    @endif
                </div>
                @endif
            </div>
        </div>
        @elseif($entry->payment == 'ONLINE' && $entry->cheque_no)
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-globe me-2"></i>Online Transfer Details</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Transaction Number:</strong><br>
                        <span class="badge bg-light text-dark fs-6">{{ $entry->cheque_no }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Transaction Date:</strong><br>
                        {{ $entry->cheque_date ? $entry->cheque_date->format('d M Y') : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Payment Items -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-list me-2"></i>Payment Breakdown</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Account</th>
                                <th>Account Code</th>
                                <th>Details</th>
                                <th class="text-end">Amount (RM)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($discountItem)
                            <tr class="table-warning">
                                <td>
                                    <i class="fas fa-percentage text-warning me-2"></i>
                                    <strong>{{ $discountItem->ledger->name }}</strong>
                                    <small class="text-muted d-block">Discount Applied</small>
                                </td>
                                <td>
                                    <code>{{ $discountItem->ledger->left_code }}/{{ $discountItem->ledger->right_code }}</code>
                                </td>
                                <td>{{ $discountItem->details ?: 'Discount amount' }}</td>
                                <td class="text-end">
                                    <span class="text-danger">-{{ number_format($discountItem->amount, 2) }}</span>
                                </td>
                            </tr>
                            @endif
                            
                            @foreach($debitItems as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->ledger->name }}</strong>
                                </td>
                                <td>
                                    <code>{{ $item->ledger->left_code }}/{{ $item->ledger->right_code }}</code>
                                </td>
                                <td>{{ $item->details ?: '-' }}</td>
                                <td class="text-end">
                                    <strong>{{ number_format($item->amount, 2) }}</strong>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-primary">
                            <tr>
                                <th colspan="3" class="text-end">Net Payment Total:</th>
                                <th class="text-end">
                                    <h5 class="mb-0">RM {{ number_format($dr_total, 2) }}</h5>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment Description -->
        @if($entry->narration)
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-comment me-2"></i>Payment Description</h6>
            </div>
            <div class="card-body">
                <div class="bg-light p-3 rounded">
                    <p class="mb-0">{{ $entry->narration }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Amount in Words -->
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="fas fa-spell-check me-2"></i>Amount in Words</h6>
            </div>
            <div class="card-body">
                <div class="bg-light p-4 rounded text-center">
                    <h4 class="mb-2 text-primary">RM {{ number_format($dr_total, 2) }}</h4>
                    <p class="mb-0 fw-medium text-uppercase">{{ strtoupper(numberToWords($dr_total)) }} ONLY</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            This payment voucher was created by {{ $entry->creator->name ?? 'System' }} 
                            on {{ $entry->created_at->format('d M Y \a\t H:i') }}
                        </small>
                    </div>
                    <div class="btn-group">
                        @if(empty($entry->inv_type))
                            @if ($permissions->contains('name', 'accounts.payment.edit'))
                            <a href="{{ route('accounts.payment.edit', $entry->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>Edit Payment
                            </a>
                            @endif
                            
                            @if ($permissions->contains('name', 'accounts.payment.create'))
                            <a href="{{ route('accounts.payment.copy', $entry->id) }}" class="btn btn-success">
                                <i class="fas fa-copy me-1"></i>Copy Payment
                            </a>
                            @endif
                        @endif
                        
                        <a href="{{ route('accounts.payment.print', $entry->id) }}" 
                           class="btn btn-warning" target="_blank">
                            <i class="fas fa-print me-1"></i>Print Voucher
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@php
function numberToWords($amount) {
    if ($amount == 0) return 'ZERO';
    
    $ones = ['', 'ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE'];
    $teens = ['TEN', 'ELEVEN', 'TWELVE', 'THIRTEEN', 'FOURTEEN', 'FIFTEEN', 'SIXTEEN', 'SEVENTEEN', 'EIGHTEEN', 'NINETEEN'];
    $tens = ['', '', 'TWENTY', 'THIRTY', 'FORTY', 'FIFTY', 'SIXTY', 'SEVENTY', 'EIGHTY', 'NINETY'];

    $toWords = function ($n) use (&$toWords, $ones, $teens, $tens) {
        if ($n == 0) return '';
        if ($n < 10) return $ones[$n];
        if ($n < 20) return $teens[$n - 10];
        if ($n < 100) return $tens[intval($n / 10)] . ($n % 10 != 0 ? ' ' . $ones[$n % 10] : '');
        if ($n < 1000) {
            $hundreds = intval($n / 100);
            $remainder = $n % 100;
            return $ones[$hundreds] . ' HUNDRED' . ($remainder ? ' ' . $toWords($remainder) : '');
        }
        if ($n < 1000000) {
            $thousands = intval($n / 1000);
            $remainder = $n % 1000;
            return $toWords($thousands) . ' THOUSAND' . ($remainder ? ' ' . $toWords($remainder) : '');
        }
        return 'TOO LARGE';
    };

    $ringgit = floor($amount);
    $sen = round(($amount - $ringgit) * 100);
    $ringgitWords = $ringgit > 0 ? $toWords($ringgit) : 'ZERO';
    $senWords = $sen > 0 ? ' AND ' . $toWords($sen) . ' SEN' : '';
    
    return "RINGGIT {$ringgitWords}{$senWords}";
}
@endphp

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Add smooth scroll for anchor links
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if( target.length ) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 1000);
        }
    });
});
</script>
@endpush
@endsection