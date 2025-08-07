@extends('layouts.app')
@section('title', 'Reconciliation Details')
@section('content')

<div class="row">
    <div class="col-12">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('accounts.reconciliation.index') }}">Bank Reconciliation</a></li>
                <li class="breadcrumb-item active">View Reconciliation</li>
            </ol>
        </nav>
        
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        <!-- Header Information -->
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">
                            <i class="fas fa-balance-scale text-primary me-2"></i>
                            Bank Reconciliation - {{ $reconciliation->month_display }}
                        </h5>
                        <small class="text-muted">{{ $reconciliation->ledger->name }}</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @php
                            $statusConfig = [
                                'draft' => ['bg-warning', 'fas fa-edit', 'Draft'],
                                'completed' => ['bg-success', 'fas fa-check', 'Completed'],
                                'locked' => ['bg-secondary', 'fas fa-lock', 'Locked']
                            ];
                            $config = $statusConfig[$reconciliation->status] ?? ['bg-secondary', 'fas fa-question', 'Unknown'];
                        @endphp
                        <span class="badge {{ $config[0] }} fs-6">
                            <i class="{{ $config[1] }} me-1"></i>{{ $config[2] }}
                        </span>
                        
                        <a href="{{ route('accounts.reconciliation.report', $reconciliation->id) }}" 
                           class="btn btn-warning btn-sm" target="_blank">
                            <i class="fas fa-print me-1"></i>Print Report
                        </a>
                        
                        <a href="{{ route('accounts.reconciliation.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-primary h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                        <h6 class="text-primary">Period</h6>
                        <h5 class="mb-0">{{ $reconciliation->month_display }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-university fa-2x text-info mb-2"></i>
                        <h6 class="text-info">Statement Balance</h6>
                        <h4 class="mb-0 text-info">RM {{ number_format($reconciliation->statement_closing_balance, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-book fa-2x text-success mb-2"></i>
                        <h6 class="text-success">Reconciled Balance</h6>
                        <h4 class="mb-0 text-success">RM {{ number_format($reconciliation->reconciled_balance, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-{{ abs($reconciliation->difference) > 0.01 ? 'danger' : 'success' }} h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-balance-scale fa-2x text-{{ abs($reconciliation->difference) > 0.01 ? 'danger' : 'success' }} mb-2"></i>
                        <h6 class="text-{{ abs($reconciliation->difference) > 0.01 ? 'danger' : 'success' }}">Difference</h6>
                        <h4 class="mb-0 text-{{ abs($reconciliation->difference) > 0.01 ? 'danger' : 'success' }}">
                            RM {{ number_format($reconciliation->difference, 2) }}
                        </h4>
                        @if(abs($reconciliation->difference) <= 0.01)
                            <small class="text-success">✓ Balanced</small>
                        @else
                            <small class="text-danger">⚠ Unbalanced</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Reconciliation Details -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Reconciliation Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="fw-medium" width="150">Bank Account:</td>
                                <td>{{ $reconciliation->ledger->name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Opening Balance:</td>
                                <td>RM {{ number_format($reconciliation->opening_balance, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Reconciled By:</td>
                                <td>
                                    @if($reconciliation->reconciledBy)
                                        <i class="fas fa-user me-1 text-muted"></i>{{ $reconciliation->reconciledBy->name }}
                                    @else
                                        <span class="text-muted">Not yet finalized</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="fw-medium" width="150">Reconciled Date:</td>
                                <td>
                                    @if($reconciliation->reconciled_date)
                                        <i class="fas fa-calendar me-1 text-muted"></i>{{ $reconciliation->reconciled_date->format('d M Y, H:i') }}
                                    @else
                                        <span class="text-muted">Not yet finalized</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Created On:</td>
                                <td>
                                    <i class="fas fa-clock me-1 text-muted"></i>{{ $reconciliation->created_at->format('d M Y, H:i') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Status:</td>
                                <td>
                                    <span class="badge {{ $config[0] }}">
                                        <i class="{{ $config[1] }} me-1"></i>{{ $config[2] }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                @if($reconciliation->notes)
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <div class="bg-light p-3 rounded">
                            <h6 class="mb-2"><i class="fas fa-sticky-note me-1"></i>Notes:</h6>
                            <p class="mb-0">{{ $reconciliation->notes }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Reconciled Transactions -->
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Reconciled Transactions</h6>
                    <span class="badge bg-light text-dark">{{ $reconciledItems->count() }} items</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if($reconciledItems->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Entry No</th>
                                <th>Particulars</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                                <th class="text-end">Running Balance</th>
                                <th>Reconciled Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $runningBalance = $reconciliation->opening_balance; @endphp
                            @foreach($reconciledItems as $item)
                                @php
                                    if($item->dc == 'D') {
                                        $runningBalance += $item->amount;
                                    } else {
                                        $runningBalance -= $item->amount;
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $item->entry->date->format('d M Y') }}</td>
                                    <td><code class="text-primary">{{ $item->entry->entry_code }}</code></td>
                                    <td>
                                        <div class="fw-medium">{{ $item->entry->narration }}</div>
                                        @if($item->details)
                                            <small class="text-muted">{{ $item->details }}</small>
                                        @endif
                                        @if($item->investigation_note)
                                            <div class="mt-1">
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-tag me-1"></i>{{ $item->investigation_note }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($item->dc == 'D')
                                            <span class="text-success fw-bold">{{ number_format($item->amount, 2) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($item->dc == 'C')
                                            <span class="text-danger fw-bold">{{ number_format($item->amount, 2) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-medium">{{ number_format($runningBalance, 2) }}</td>
                                    <td>
                                        @if($item->reconciliation_date)
                                            {{ \Carbon\Carbon::parse($item->reconciliation_date)->format('d M Y') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-success">
                            <tr>
                                <th colspan="5" class="text-end">Closing Reconciled Balance:</th>
                                <th class="text-end">
                                    <h6 class="mb-0">RM {{ number_format($runningBalance, 2) }}</h6>
                                </th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0">No transactions have been reconciled yet.</p>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Adjustments -->
        @if($reconciliation->adjustments->count() > 0)
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-edit me-2"></i>Reconciliation Adjustments</h6>
                    <span class="badge bg-dark">{{ $reconciliation->adjustments->count() }} adjustments</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                                <th>Entry Reference</th>
                                <th>Created By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reconciliation->adjustments as $adjustment)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $adjustment->type == 'debit' ? 'success' : 'danger' }}">
                                        {{ ucfirst($adjustment->type) }}
                                    </span>
                                </td>
                                <td>{{ $adjustment->description }}</td>
                                <td class="text-end fw-bold">RM {{ number_format($adjustment->amount, 2) }}</td>
                                <td>
                                    @if($adjustment->entry)
                                        <a href="{{ route('accounts.journal.view', $adjustment->entry->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-external-link-alt me-1"></i>{{ $adjustment->entry->entry_code }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($adjustment->creator)
                                        <i class="fas fa-user me-1 text-muted"></i>{{ $adjustment->creator->name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $adjustment->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Action Buttons -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            This reconciliation contains {{ $reconciledItems->count() }} reconciled transaction(s)
                            @if($reconciliation->adjustments->count() > 0)
                                and {{ $reconciliation->adjustments->count() }} adjustment(s)
                            @endif
                            and is {{ abs($reconciliation->difference) <= 0.01 ? 'properly balanced' : 'not balanced' }}.
                        </small>
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('accounts.reconciliation.report', $reconciliation->id) }}" 
                           class="btn btn-success" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i>Generate Report
                        </a>
                        
                        @if($reconciliation->status == 'completed')
                            <button type="button" class="btn btn-warning" onclick="showLockModal()">
                                <i class="fas fa-lock me-1"></i>Lock Reconciliation
                            </button>
                        @endif
                        
                        <a href="{{ route('accounts.reconciliation.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lock Confirmation Modal -->
<div class="modal fade" id="lockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-lock me-2"></i>Lock Reconciliation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> Once locked, this reconciliation cannot be modified without administrator privileges.
                </div>
                <p>Are you sure you want to lock this reconciliation?</p>
                <ul class="small text-muted">
                    <li>All reconciled transactions will be permanently marked</li>
                    <li>No further changes can be made to this reconciliation</li>
                    <li>This action requires administrator privileges to undo</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="lockReconciliation()">
                    <i class="fas fa-lock me-1"></i>Lock Reconciliation
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showLockModal() {
    new bootstrap.Modal(document.getElementById('lockModal')).show();
}

function lockReconciliation() {
    fetch(`/accounts/reconciliation/{{ $reconciliation->id }}/lock`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Reconciliation locked successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('Error locking reconciliation', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error locking reconciliation', 'danger');
    });
    
    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('lockModal')).hide();
}

function showAlert(message, type = 'info') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; max-width: 400px;">
            <i class="fas fa-info-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('body').append(alertHtml);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 5000);
}

// Initialize tooltips
$(document).ready(function() {
    $('[title]').tooltip();
});
</script>
@endpush
@endsection