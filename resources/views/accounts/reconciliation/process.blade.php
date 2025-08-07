@extends('layouts.app')
@section('title', 'Bank Reconciliation Process')
@section('content')

<div class="row">
    <div class="col-12">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('accounts.reconciliation.index') }}">Bank Reconciliation</a></li>
                <li class="breadcrumb-item active">Reconcile {{ $reconciliation->ledger->name }}</li>
            </ol>
        </nav>
        
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
                    <span class="badge bg-info fs-6">In Progress</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center p-2 bg-light rounded">
                            <h6 class="text-muted mb-1">Opening Balance</h6>
                            <h5 class="mb-0 text-primary">RM {{ number_format($reconciliation->opening_balance, 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-2 bg-light rounded">
                            <h6 class="text-muted mb-1">Statement Balance</h6>
                            <h5 class="mb-0 text-info">RM {{ number_format($reconciliation->statement_closing_balance, 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-2 bg-light rounded">
                            <h6 class="text-muted mb-1">Reconciled Balance</h6>
                            <h5 class="mb-0 text-success" id="reconciledBalanceDisplay">RM {{ number_format($reconciliation->reconciled_balance, 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-2 bg-light rounded">
                            <h6 class="text-muted mb-1">Difference</h6>
                            <h5 class="mb-0" id="differenceDisplay" style="color: {{ abs($reconciliation->difference) > 0.01 ? 'red' : 'green' }}">
                                RM {{ number_format($reconciliation->difference, 2) }}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Reconciliation Status & Actions -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Reconciliation Status & Actions</h6>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Statement Balance</label>
                            <div class="input-group">
                                <span class="input-group-text">RM</span>
                                <input type="text" class="form-control" id="statementBalance"
                                       value="{{ number_format($reconciliation->statement_closing_balance, 2) }}" readonly>
                                @if($reconciliation->status == 'draft')
                                <button class="btn btn-outline-primary" onclick="editBalance()" title="Edit Balance">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endif
                            </div>
                            <div id="balanceEdit" style="display: none;" class="mt-2">
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" class="form-control" id="newBalance" 
                                           value="{{ $reconciliation->statement_closing_balance }}" step="0.01">
                                    <button class="btn btn-success" onclick="saveBalance()">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-danger" onclick="cancelEdit()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div id="balanceStatus">
                            @if(abs($reconciliation->difference) > 0.01)
                                <div class="alert alert-danger mb-0">
                                    <h6 class="mb-1"><i class="fas fa-exclamation-triangle me-1"></i>Not Balanced</h6>
                                    <p class="mb-0 small">Difference: RM {{ number_format(abs($reconciliation->difference), 2) }}</p>
                                </div>
                            @else
                                <div class="alert alert-success mb-0">
                                    <h6 class="mb-1"><i class="fas fa-check-circle me-1"></i>Balanced</h6>
                                    <p class="mb-0 small">Ready to finalize</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-md-4 text-end">
                        <div id="actionButtons">
                            @if(abs($reconciliation->difference) > 0.01)
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#adjustmentModal">
                                    <i class="fas fa-plus me-1"></i>Add Adjustment
                                </button>
                            @else
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#finalizeModal">
                                    <i class="fas fa-check me-1"></i>Finalize Reconciliation
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Current Month Transactions -->
        <div class="card mb-3">
            <div class="card-header bg-danger text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-list me-2"></i>Current Month Transactions</h6>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="selectAllCurrent()">
                            <i class="fas fa-check-square me-1"></i>Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="clearAllCurrent()">
                            <i class="fas fa-square me-1"></i>Clear All
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="currentTransactions">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">
                                    <input type="checkbox" id="selectAllCurrentCheckbox" class="form-check-input">
                                </th>
                                <th width="10%">Date</th>
                                <th width="15%">Entry No</th>
                                <th width="35%">Particulars</th>
                                <th width="10%" class="text-end">Debit</th>
                                <th width="10%" class="text-end">Credit</th>
                                <th width="10%" class="text-end">Balance</th>
                                <th width="5%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $runningBalance = $reconciliation->opening_balance; @endphp
                            @foreach($transactions as $transaction)
                                @php
                                    if($transaction->dc == 'D') {
                                        $runningBalance += $transaction->amount;
                                    } else {
                                        $runningBalance -= $transaction->amount;
                                    }
                                @endphp
                                <tr data-item-id="{{ $transaction->id }}" 
                                    class="{{ $transaction->is_reconciled ? 'table-success' : '' }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input reconcile-item" 
                                               value="{{ $transaction->id }}"
                                               {{ $transaction->is_reconciled ? 'checked' : '' }}>
                                    </td>
                                    <td>{{ $transaction->entry->date->format('d M Y') }}</td>
                                    <td><code class="text-primary">{{ $transaction->entry->entry_code }}</code></td>
                                    <td>
                                        <div class="fw-medium">{{ $transaction->entry->narration }}</div>
                                        @if($transaction->details)
                                            <small class="text-muted">{{ $transaction->details }}</small>
                                        @endif
                                        @if($transaction->investigation_note)
                                            <div class="mt-1">
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-tag me-1"></i>{{ $transaction->investigation_note }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($transaction->dc == 'D')
                                            <span class="text-success fw-bold">{{ number_format($transaction->amount, 2) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($transaction->dc == 'C')
                                            <span class="text-danger fw-bold">{{ number_format($transaction->amount, 2) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-medium">{{ number_format($runningBalance, 2) }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="addInvestigationNote({{ $transaction->id }})"
                                                title="Add Investigation Note">
                                            <i class="fas fa-tag"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Pending Transactions from Previous Months -->
        @if($pendingTransactions->count() > 0)
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Pending Transactions from Previous Months</h6>
                    <span class="badge bg-dark">{{ $pendingTransactions->count() }} items</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="pendingTransactions">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">
                                    <input type="checkbox" id="selectAllPending" class="form-check-input">
                                </th>
                                <th width="10%">Date</th>
                                <th width="15%">Entry No</th>
                                <th width="40%">Particulars</th>
                                <th width="12%" class="text-end">Debit</th>
                                <th width="12%" class="text-end">Credit</th>
                                <th width="6%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingTransactions as $transaction)
                                <tr data-item-id="{{ $transaction->id }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input reconcile-item" 
                                               value="{{ $transaction->id }}">
                                    </td>
                                    <td>{{ $transaction->entry->date->format('d M Y') }}</td>
                                    <td><code class="text-primary">{{ $transaction->entry->entry_code }}</code></td>
                                    <td>
                                        <div class="fw-medium">{{ $transaction->entry->narration }}</div>
                                        @if($transaction->details)
                                            <small class="text-muted">{{ $transaction->details }}</small>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($transaction->dc == 'D')
                                            <span class="text-success fw-bold">{{ number_format($transaction->amount, 2) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($transaction->dc == 'C')
                                            <span class="text-danger fw-bold">{{ number_format($transaction->amount, 2) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="addInvestigationNote({{ $transaction->id }})"
                                                title="Add Investigation Note">
                                            <i class="fas fa-tag"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Adjustment Modal -->
<div class="modal fade" id="adjustmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('accounts.reconciliation.adjustment', $reconciliation->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle text-primary me-2"></i>Create Manual Adjustment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Adjustment Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="debit">Debit (Increase Balance)</option>
                            <option value="credit">Credit (Decrease Balance)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Adjustment Ledger <span class="text-danger">*</span></label>
                        <select name="adjustment_ledger_id" class="form-control" required>
                            <option value="">Select Ledger</option>
                            @php
                                $adjustmentLedgers = \App\Models\Ledger::where('type', 0)->orderBy('name')->get();
                            @endphp
                            @foreach($adjustmentLedgers as $ledger)
                                <option value="{{ $ledger->id }}">
                                    {{ $ledger->left_code }}/{{ $ledger->right_code }} - {{ $ledger->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" name="amount" class="form-control" 
                                   step="0.01" min="0.01" required placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="3" required
                                  placeholder="Enter description for this adjustment"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Investigation Note Modal -->
<div class="modal fade" id="investigationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-tag text-warning me-2"></i>Add Investigation Note
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="investigationItemId">
                <div class="mb-3">
                    <label class="form-label">Investigation Note <span class="text-danger">*</span></label>
                    <textarea id="investigationNote" class="form-control" rows="3" required
                              placeholder="Enter your investigation note here..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveInvestigationNote()">Save Note</button>
            </div>
        </div>
    </div>
</div>

<!-- Finalize Modal -->
<div class="modal fade" id="finalizeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('accounts.reconciliation.finalize', $reconciliation->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>Finalize Reconciliation
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle me-2"></i>
                        The reconciliation is balanced and ready to be finalized.
                    </div>
                    <p>Are you sure you want to finalize this reconciliation? This action cannot be undone without administrator privileges.</p>
                    <div class="mb-3">
                        <label class="form-label">Additional Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Enter any additional notes or comments"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Finalize Reconciliation</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Select all checkboxes for current transactions
    $('#selectAllCurrentCheckbox').change(function() {
        $('#currentTransactions .reconcile-item').prop('checked', $(this).prop('checked'));
        updateReconciliation();
    });
    
    // Select all checkboxes for pending transactions
    $('#selectAllPending').change(function() {
        $('#pendingTransactions .reconcile-item').prop('checked', $(this).prop('checked'));
        updateReconciliation();
    });
    
    // Individual checkbox change
    $('.reconcile-item').change(function() {
        updateReconciliation();
    });
    
    // Update reconciliation via AJAX
    function updateReconciliation() {
        var selectedItems = [];
        $('.reconcile-item:checked').each(function() {
            selectedItems.push($(this).val());
        });
        
        $.ajax({
            url: "{{ route('accounts.reconciliation.update-items', $reconciliation->id) }}",
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                items: selectedItems
            },
            success: function(response) {
                // Update displays
                $('#reconciledBalanceDisplay').text('RM ' + parseFloat(response.reconciled_balance).toLocaleString('en-US', {minimumFractionDigits: 2}));
                $('#differenceDisplay').text('RM ' + parseFloat(response.difference).toLocaleString('en-US', {minimumFractionDigits: 2}));
                
                // Update colors and status
                if (Math.abs(response.difference) > 0.01) {
                    $('#differenceDisplay').css('color', 'red');
                    updateBalanceStatus(false, response.difference);
                    updateActionButtons(false);
                } else {
                    $('#differenceDisplay').css('color', 'green');
                    updateBalanceStatus(true, response.difference);
                    updateActionButtons(true);
                }
                
                // Update row highlighting
                $('.reconcile-item').each(function() {
                    if ($(this).prop('checked')) {
                        $(this).closest('tr').addClass('table-success');
                    } else {
                        $(this).closest('tr').removeClass('table-success');
                    }
                });
                
                showAlert('Reconciliation updated successfully', 'success');
            },
            error: function(xhr) {
                showAlert('Error updating reconciliation: ' + xhr.responseJSON.error, 'danger');
            }
        });
    }
    
    function updateBalanceStatus(isBalanced, difference) {
        const statusHtml = isBalanced 
            ? `<div class="alert alert-success mb-0">
                 <h6 class="mb-1"><i class="fas fa-check-circle me-1"></i>Balanced</h6>
                 <p class="mb-0 small">Ready to finalize</p>
               </div>`
            : `<div class="alert alert-danger mb-0">
                 <h6 class="mb-1"><i class="fas fa-exclamation-triangle me-1"></i>Not Balanced</h6>
                 <p class="mb-0 small">Difference: RM ${Math.abs(difference).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
               </div>`;
        $('#balanceStatus').html(statusHtml);
    }
    
    function updateActionButtons(isBalanced) {
        const buttonsHtml = isBalanced
            ? `<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#finalizeModal">
                 <i class="fas fa-check me-1"></i>Finalize Reconciliation
               </button>`
            : `<button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#adjustmentModal">
                 <i class="fas fa-plus me-1"></i>Add Adjustment
               </button>`;
        $('#actionButtons').html(buttonsHtml);
    }
});

function selectAllCurrent() {
    $('#currentTransactions .reconcile-item').prop('checked', true);
    $('#selectAllCurrentCheckbox').prop('checked', true);
    updateReconciliation();
}

function clearAllCurrent() {
    $('#currentTransactions .reconcile-item').prop('checked', false);
    $('#selectAllCurrentCheckbox').prop('checked', false);
    updateReconciliation();
}

function addInvestigationNote(itemId) {
    $('#investigationItemId').val(itemId);
    $('#investigationNote').val('');
    new bootstrap.Modal(document.getElementById('investigationModal')).show();
}

function saveInvestigationNote() {
    var itemId = $('#investigationItemId').val();
    var note = $('#investigationNote').val();
    
    if (!note.trim()) {
        showAlert('Please enter a note', 'warning');
        return;
    }
    
    $.ajax({
        url: "{{ route('accounts.reconciliation.investigation-note', $reconciliation->id) }}",
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            item_id: itemId,
            note: note
        },
        success: function(response) {
            bootstrap.Modal.getInstance(document.getElementById('investigationModal')).hide();
            location.reload();
        },
        error: function(xhr) {
            showAlert('Error saving note: ' + xhr.responseJSON.error, 'danger');
        }
    });
}

function editBalance() {
    $('#statementBalance').hide();
    $('#balanceEdit').show();
}

function cancelEdit() {
    $('#statementBalance').show();
    $('#balanceEdit').hide();
    $('#newBalance').val({{ $reconciliation->statement_closing_balance }});
}

function saveBalance() {
    var newBalance = $('#newBalance').val();
    
    if (!newBalance || newBalance <= 0) {
        showAlert('Please enter a valid balance', 'warning');
        return;
    }
    
    $.ajax({
        url: "{{ route('accounts.reconciliation.update-balance', $reconciliation->id) }}",
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            statement_closing_balance: newBalance
        },
        success: function(response) {
            location.reload();
        },
        error: function(xhr) {
            showAlert('Error updating balance: ' + xhr.responseJSON.error, 'danger');
        }
    });
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