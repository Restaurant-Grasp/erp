@extends('layouts.app')
@section('title', 'Edit Journal Entry')
@section('content')

<div class="row">
    <div class="col-12">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('accounts.journal.list') }}">Journal List</a></li>
                <li class="breadcrumb-item active">Edit Journal Entry</li>
            </ol>
        </nav>

        <form method="POST" action="{{ route('accounts.journal.update', $entry->id) }}" id="journalForm" onsubmit="return validateAndSubmit()">
            @csrf
            @method('PUT')
            
            <!-- Header Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-edit text-warning me-2"></i>
                        <h5 class="mb-0">Edit Journal Entry - <code class="text-primary">{{ $entry->entry_code }}</code></h5>
                        @if($entry->isBalanced())
                            <span class="badge bg-success ms-3">Balanced</span>
                        @else
                            <span class="badge bg-danger ms-3">Unbalanced</span>
                        @endif
                    </div>
                </div>
                
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Original Entry Info -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <strong><i class="fas fa-calendar-alt me-1"></i>Created:</strong><br>
                                {{ $entry->created_at->format('d M Y, H:i') }}
                            </div>
                            <div class="col-md-4">
                                <strong><i class="fas fa-user me-1"></i>Created By:</strong><br>
                                {{ $entry->creator->name ?? 'System' }}
                            </div>
                            <div class="col-md-4">
                                <strong><i class="fas fa-clock me-1"></i>Last Updated:</strong><br>
                                {{ $entry->updated_at->format('d M Y, H:i') }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control" 
                                       value="{{ old('date', $entry->date->format('Y-m-d')) }}" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Entry Code <span class="text-danger">*</span></label>
                                <input type="text" name="entry_code" class="form-control bg-light" readonly
                                       value="{{ old('entry_code', $entry->entry_code) }}" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Fund <span class="text-danger">*</span></label>
                                <select name="fund_id" class="form-control" required>
                                    @foreach($funds as $fund)
                                        <option value="{{ $fund->id }}" {{ old('fund_id', $entry->fund_id) == $fund->id ? 'selected' : '' }}>
                                            {{ $fund->name }}{{ $fund->code ? ' (' . $fund->code . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Journal Items -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-plus me-2"></i>Add More Journal Items</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Account <span class="text-danger">*</span></label>
                                <select class="form-control" id="ledgerSelect">
                                    <option value="">Select Account</option>
                                    @foreach($ledgers as $ledger)
                                        <option value="{{ $ledger->id }}" 
                                                data-code="{{ $ledger->left_code }}/{{ $ledger->right_code }}" 
                                                data-name="{{ $ledger->name }}">
                                            {{ $ledger->left_code }}/{{ $ledger->right_code }} - {{ $ledger->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">Debit Amount</label>
                                <div class="input-group">
                              
                                    <input type="number" id="tempDrAmount" class="form-control" 
                                           step="0.01" min="0" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">Credit Amount</label>
                                <div class="input-group">
                                
                                    <input type="number" id="tempCrAmount" class="form-control" 
                                           step="0.01" min="0" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end my-3">
                            <button type="button" class="btn btn-primary w-100" id="addJournalItem">
                                <i class="fas fa-plus me-1"></i>Add Item
                            </button>
                        </div>
                    </div>
                    
                   
                    
                </div>
            </div>
            
            <!-- Journal Items Table -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-list me-2"></i>Journal Items</h6>
                        <small class="text-white-50">
                            Original Total: DR {{ number_format($entry->dr_total, 2) }} / CR {{ number_format($entry->cr_total, 2) }}
                        </small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="journalItemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="50%">Account</th>
                                    <th width="20%" class="text-end">Debit (RM)</th>
                                    <th width="20%" class="text-end">Credit (RM)</th>
                                    <th width="10%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Pre-populated items will be added here by JavaScript -->
                            </tbody>
                            <tfoot class="table-primary">
                                <tr>
                                    <th>Total:</th>
                                    <th class="text-end"><span id="totalDebit">0.00</span></th>
                                    <th class="text-end"><span id="totalCredit">0.00</span></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Balance Status -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="alert alert-warning" id="balanceStatus">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Total Debit:</strong> RM <span id="debitBalance">0.00</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Total Credit:</strong> RM <span id="creditBalance">0.00</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Difference:</strong> RM <span id="difference" class="text-danger">0.00</span>
                                    </div>
                                    <div class="col-md-3">
                                        <span id="balanceStatusText" class="badge bg-warning">Not Balanced</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Description -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-comment me-2"></i>Journal Description</h6>
                </div>
                <div class="card-body">
                    <textarea name="narration" class="form-control" rows="4" 
                              placeholder="Enter detailed description of the journal entry">{{ old('narration', $entry->narration) }}</textarea>
                </div>
            </div>
            
            <!-- Submit Buttons -->
        
                    <div class="d-flex gap-3  mb-4">
                         <div>                           
                            <button type="submit" class="btn btn-primary" id="saveButton" disabled>
                                <i class="fas fa-save me-1"></i>Update Journal Entry
                            </button>
                        </div>
                        <a href="{{ route('accounts.journal.list') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                       
                    </div>
              
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let itemIndex = 0;
    
    // Pre-populate existing items
    @foreach($entry->entryItems as $item)
        addItemToTable(
            '{{ $item->ledger_id }}',
            '{{ $item->ledger->left_code }}/{{ $item->ledger->right_code }} - {{ $item->ledger->name }}',
            {{ $item->dc == 'D' ? $item->amount : 0 }},
            {{ $item->dc == 'C' ? $item->amount : 0 }}
        );
    @endforeach
    
    // Add journal item
    $('#addJournalItem').click(function() {
        const ledgerId = $('#ledgerSelect').val();
        const ledgerText = $('#ledgerSelect option:selected').text();
        const drAmount = parseFloat($('#tempDrAmount').val()) || 0.00;
        const crAmount = parseFloat($('#tempCrAmount').val()) || 0.00;
        
        if (!ledgerId) {
            showAlert('Please select an account', 'warning');
            return;
        }
        
        if (drAmount === 0 && crAmount === 0) {
            showAlert('Please enter either debit or credit amount', 'warning');
            return;
        }
        
        if (drAmount > 0 && crAmount > 0) {
            showAlert('Please enter either debit or credit amount, not both', 'warning');
            return;
        }
        
        addItemToTable(ledgerId, ledgerText, drAmount, crAmount);
        
        // Reset form
        $('#ledgerSelect').val('');
        $('#tempDrAmount, #tempCrAmount').val('');
        $('#ledgerSelect').focus();
    });
    
    function addItemToTable(ledgerId, ledgerText, drAmount, crAmount) {
        const row = `
            <tr data-index="${itemIndex}">
                <td>
                    <strong>${ledgerText}</strong>
                    <input type="hidden" name="journal_items[${itemIndex}][ledger_id]" value="${ledgerId}">
                </td>
                <td class="text-end">
                    <span class="fw-medium">${drAmount.toFixed(2)}</span>
                    <input type="hidden" name="journal_items[${itemIndex}][dr_amount]" value="${drAmount}">
                </td>
                <td class="text-end">
                    <span class="fw-medium">${crAmount.toFixed(2)}</span>
                    <input type="hidden" name="journal_items[${itemIndex}][cr_amount]" value="${crAmount}">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove-journal-item" title="Remove Item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#journalItemsTable tbody').append(row);
        itemIndex++;
        calculateTotals();
        updateBalanceStatus();
    }
    
    // Remove journal item
    $(document).on('click', '.remove-journal-item', function() {
        $(this).closest('tr').remove();
        calculateTotals();
        updateBalanceStatus();
    });
    
    function calculateTotals() {
        let totalDr = 0;
        let totalCr = 0;
        
        $('input[name*="[dr_amount]"]').each(function() {
            totalDr += parseFloat($(this).val()) || 0;
        });
        
        $('input[name*="[cr_amount]"]').each(function() {
            totalCr += parseFloat($(this).val()) || 0;
        });
        
        $('#totalDebit').text(totalDr.toFixed(2));
        $('#totalCredit').text(totalCr.toFixed(2));
        $('#debitBalance').text(totalDr.toFixed(2));
        $('#creditBalance').text(totalCr.toFixed(2));
        
        const difference = Math.abs(totalDr - totalCr);
        $('#difference').text(difference.toFixed(2));
        
        return { totalDr, totalCr, difference };
    }
    
    function updateBalanceStatus() {
        const { totalDr, totalCr, difference } = calculateTotals();
        const itemCount = $('#journalItemsTable tbody tr').length;
        
        if (difference < 0.01 && totalDr > 0 && itemCount >= 2) {
            $('#saveButton').prop('disabled', false);
            $('#difference').removeClass('text-danger').addClass('text-success');
            $('#balanceStatus').removeClass('alert-warning alert-danger').addClass('alert-success');
            $('#balanceStatusText').removeClass('bg-warning bg-danger').addClass('bg-success')
                                 .html('<i class="fas fa-check me-1"></i>Balanced');
        } else {
            $('#saveButton').prop('disabled', true);
            $('#difference').removeClass('text-success').addClass('text-danger');
            
            if (itemCount < 2) {
                $('#balanceStatus').removeClass('alert-success alert-danger').addClass('alert-warning');
                $('#balanceStatusText').removeClass('bg-success bg-danger').addClass('bg-warning')
                                     .html('<i class="fas fa-info-circle me-1"></i>Need 2+ Items');
            } else {
                $('#balanceStatus').removeClass('alert-success alert-warning').addClass('alert-danger');
                $('#balanceStatusText').removeClass('bg-success bg-warning').addClass('bg-danger')
                                     .html('<i class="fas fa-exclamation-triangle me-1"></i>Not Balanced');
            }
        }
    }
    
    // Allow Enter key in amount fields
    $('#tempDrAmount, #tempCrAmount').keypress(function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#addJournalItem').click();
        }
    });
    
    // Clear opposite amount when typing
    $('#tempDrAmount').on('input', function() {
        if ($(this).val() && parseFloat($(this).val()) > 0) {
            $('#tempCrAmount').val('');
        }
    });
    
    $('#tempCrAmount').on('input', function() {
        if ($(this).val() && parseFloat($(this).val()) > 0) {
            $('#tempDrAmount').val('');
        }
    });
    
    // Form validation and submission
    window.validateAndSubmit = function() {
        const form = document.getElementById('journalForm');
        const submitBtn = document.getElementById('saveButton');
        const itemCount = $('#journalItemsTable tbody tr').length;
        
        if (itemCount < 2) {
            showAlert('Journal entry must have at least 2 line items', 'danger');
            return false;
        }
        
        if (form.checkValidity()) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
            return true;
        }
        return false;
    };
    
    function showAlert(message, type = 'info') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show mt-2" role="alert">
                <i class="fas fa-info-circle me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('.card-body:first').prepend(alertHtml);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }
    
    // Initialize calculation
    calculateTotals();
    updateBalanceStatus();
    
    // Initialize tooltips
    $('[title]').tooltip();
});
</script>
@endpush
@endsection