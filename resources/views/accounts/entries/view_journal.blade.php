@extends('layouts.app')
@section('title', 'Journal Entry Details')
@section('content')

<div class="row">
    <div class="col-12">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('accounts.journal.list') }}">Journal List</a></li>
                <li class="breadcrumb-item active">Journal Details</li>
            </ol>
        </nav>

        <!-- Header Card -->
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">
                            <i class="fas fa-book text-primary me-2"></i>
                            Journal Entry - <code class="text-primary">{{ $entry->entry_code }}</code>
                        </h5>
                        <small class="text-muted">Created on {{ $entry->created_at->format('d M Y, H:i') }}</small>
                    </div>
                    <div class="btn-group">
                        @php
                        $role = auth()->user()->getRoleNames()->first();
                        $permissions = getCurrentRolePermissions($role);
                        @endphp
                        
                        <a href="{{ route('accounts.journal.print', $entry->id) }}" 
                           class="btn btn-warning btn-sm" target="_blank">
                            <i class="fas fa-print me-1"></i>Print
                        </a>
                        
                        @if(empty($entry->inv_type))
                            @if ($permissions->contains('name', 'accounts.journal.edit'))
                            <a href="{{ route('accounts.journal.edit', $entry->id) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit me-1"></i>Edit
                            </a>
                            @endif
                            
                            @if ($permissions->contains('name', 'accounts.journal.create'))
                            <a href="{{ route('accounts.journal.copy', $entry->id) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-copy me-1"></i>Copy
                            </a>
                            @endif
                        @endif
                        
                        <a href="{{ route('accounts.journal.list') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Journal Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-primary h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                        <h6 class="text-primary">Entry Date</h6>
                        <h5 class="mb-0">{{ $entry->date->format('d M Y') }}</h5>
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
                <div class="card border-success h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-balance-scale fa-2x text-success mb-2"></i>
                        <h6 class="text-success">Balance Status</h6>
                        @if($entry->isBalanced())
                            <span class="badge bg-success fs-6">
                                <i class="fas fa-check me-1"></i>Balanced
                            </span>
                            <br><small class="text-success">Perfect balance achieved</small>
                        @else
                            <span class="badge bg-danger fs-6">
                                <i class="fas fa-exclamation-triangle me-1"></i>Unbalanced
                            </span>
                            <br><small class="text-danger">Difference: RM {{ number_format(abs($entry->dr_total - $entry->cr_total), 2) }}</small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-list fa-2x text-warning mb-2"></i>
                        <h6 class="text-warning">Total Entries</h6>
                        <h4 class="mb-0 text-warning">{{ $entry->entryItems->count() }}</h4>
                        <small class="text-muted">Journal items</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Journal Items -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-list me-2"></i>Journal Entry Details</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Account</th>
                                <th>Account Code</th>
                                <th class="text-end">Debit (RM)</th>
                                <th class="text-end">Credit (RM)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entry->entryItems as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->ledger->name }}</strong>
                                </td>
                                <td>
                                    <code>{{ $item->ledger->left_code }}/{{ $item->ledger->right_code }}</code>
                                </td>
                                <td class="text-end">
                                    @if($item->dc == 'D')
                                        <span class="fw-bold text-success">{{ number_format($item->amount, 2) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($item->dc == 'C')
                                        <span class="fw-bold text-primary">{{ number_format($item->amount, 2) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-primary">
                            <tr>
                                <th colspan="2">Total:</th>
                                <th class="text-end">
                                    <h5 class="mb-0 text-success">{{ number_format($entry->dr_total, 2) }}</h5>
                                </th>
                                <th class="text-end">
                                    <h5 class="mb-0 text-primary">{{ number_format($entry->cr_total, 2) }}</h5>
                                </th>
                            </tr>
                            @if(!$entry->isBalanced())
                            <tr class="table-danger">
                                <th colspan="2">Difference:</th>
                                <th colspan="2" class="text-end">
                                    <h6 class="mb-0 text-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ number_format(abs($entry->dr_total - $entry->cr_total), 2) }}
                                    </h6>
                                </th>
                            </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Balance Visualization -->
        @if($entry->isBalanced())
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                            <h4 class="text-success mb-1">RM {{ number_format($entry->dr_total, 2) }}</h4>
                            <p class="mb-0 text-success"><strong>Total Debits</strong></p>
                        </div>
                    </div>
                    <div class="col-md-2 text-center">
                        <i class="fas fa-equals fa-2x text-success"></i>
                        <p class="mt-2 mb-0 text-success"><strong>BALANCED</strong></p>
                    </div>
                    <div class="col-md-5">
                        <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                            <h4 class="text-primary mb-1">RM {{ number_format($entry->cr_total, 2) }}</h4>
                            <p class="mb-0 text-primary"><strong>Total Credits</strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="card mb-3 border-danger">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                            <h4 class="text-success mb-1">RM {{ number_format($entry->dr_total, 2) }}</h4>
                            <p class="mb-0 text-success"><strong>Total Debits</strong></p>
                        </div>
                    </div>
                    <div class="col-md-2 text-center">
                        <i class="fas fa-not-equal fa-2x text-danger"></i>
                        <p class="mt-2 mb-0 text-danger"><strong>NOT BALANCED</strong></p>
                        <small class="text-danger">Diff: {{ number_format(abs($entry->dr_total - $entry->cr_total), 2) }}</small>
                    </div>
                    <div class="col-md-5">
                        <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                            <h4 class="text-primary mb-1">RM {{ number_format($entry->cr_total, 2) }}</h4>
                            <p class="mb-0 text-primary"><strong>Total Credits</strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Journal Description -->
        @if($entry->narration)
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-comment me-2"></i>Journal Description</h6>
            </div>
            <div class="card-body">
                <div class="bg-light p-3 rounded">
                    <p class="mb-0">{{ $entry->narration }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Entry Audit Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Entry Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="fw-medium" width="150">Created By:</td>
                                <td>
                                    <i class="fas fa-user me-1 text-muted"></i>
                                    {{ $entry->creator->name ?? 'System' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Created On:</td>
                                <td>
                                    <i class="fas fa-clock me-1 text-muted"></i>
                                    {{ $entry->created_at->format('d M Y \a\t H:i:s') }}
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="fw-medium" width="150">Last Updated:</td>
                                <td>
                                    <i class="fas fa-edit me-1 text-muted"></i>
                                    {{ $entry->updated_at->format('d M Y \a\t H:i:s') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Entry Type:</td>
                                <td>
                                    <i class="fas fa-tag me-1 text-muted"></i>
                                    {{ $entry->inv_type ? 'System Generated' : 'Manual Entry' }}
                                </td>
                            </tr>
                        </table>
                    </div>
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
                            This journal entry contains {{ $entry->entryItems->count() }} line item(s) 
                            and is {{ $entry->isBalanced() ? 'properly balanced' : 'not balanced' }}.
                        </small>
                    </div>
                    <div class="btn-group">
                        @if(empty($entry->inv_type))
                            @if ($permissions->contains('name', 'accounts.journal.edit'))
                            <a href="{{ route('accounts.journal.edit', $entry->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>Edit Entry
                            </a>
                            @endif
                            
                            @if ($permissions->contains('name', 'accounts.journal.create'))
                            <a href="{{ route('accounts.journal.copy', $entry->id) }}" class="btn btn-success">
                                <i class="fas fa-copy me-1"></i>Copy Entry
                            </a>
                            @endif
                        @endif
                        
                        <a href="{{ route('accounts.journal.print', $entry->id) }}" 
                           class="btn btn-warning" target="_blank">
                            <i class="fas fa-print me-1"></i>Print Entry
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

    // Add visual feedback for balanced vs unbalanced entries
    @if($entry->isBalanced())
        // Add subtle animation for balanced entries
        $('.bg-success').addClass('animate__animated animate__fadeIn');
    @else
        // Add pulse effect for unbalanced entries
        $('.text-danger').addClass('animate__animated animate__pulse');
    @endif
});
</script>
@endpush
@endsection