@extends('layouts.app')
@section('title', 'Edit Ledger')

@section('content')
<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb pl-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('chart_of_accounts.index') }}">Chart of Accounts</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Ledger</li>
            </ol>
        </nav>
        <br>
        <div class="card">
            <div class="card-header">
                <h5>Edit Ledger: {{ $ledger->name }}</h5>
            </div>
            
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
         
                <form method="POST" action="{{ route('chart_of_accounts.ledger.update', $ledger->id) }}" id="ledgerForm">
                    @csrf
                    @method('PUT')
                    
                    <!-- Basic Information Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="section-title"> Basic Information</h6>
                            <hr>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Ledger Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $ledger->name) }}" required 
                                       placeholder="Enter ledger name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                               
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="group_id" class="form-label">Under Group <span class="text-danger">*</span></label>
                                <select class="form-select @error('group_id') is-invalid @enderror" 
                                        id="group_id" name="group_id" required onchange="updateLeftCode()">
                                    <option value="">-- Select Group --</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" data-code="{{ $group->code }}"
                                                {{ old('group_id', $ledger->group_id) == $group->id ? 'selected' : '' }}>
                                            {!! $group->display_name !!}
                                        </option>
                                    @endforeach
                                </select>
                                @error('group_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                               
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Ledger Code</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="left_code" 
                                           value="{{ $ledger->left_code }}" placeholder="Group Code" 
                                           readonly style="max-width: 120px; background-color: #f8f9fa;">
                                    <span class="input-group-text">/</span>
                                    <input type="text" class="form-control @error('right_code') is-invalid @enderror" 
                                           id="right_code" name="right_code" placeholder="Enter code" 
                                           value="{{ old('right_code', $ledger->right_code) }}" maxlength="4" required>
                                    @error('right_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text">Right code will be auto-padded with zeros (e.g., 1 → 0001)</div>
                            </div>
                        </div>
                        
                  
                    </div>
                    
                    <!-- Ledger Features Section -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="section-title">Ledger Features</h6>
                            <hr>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                         
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_bank" name="is_bank" 
                                           {{ old('is_bank', $ledger->type) == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_bank">
                                        <span>Bank/Cash Account</span>
                                    </label>
                                </div>
              
                        
                        </div>
                        
                        <div class="col-md-4">
              
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="reconciliation" name="reconciliation"
                                           {{ old('reconciliation', $ledger->reconciliation) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="reconciliation">
                                        <span>Enable Reconciliation</span>
                                    </label>
                             
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                  
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="pa" name="pa"
                                           {{ old('pa', $ledger->pa) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="pa">
                                        <span>P&L Accumulation</span>
                                    </label>
                                </div>
                                
                 
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
          
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="aging" name="aging"
                                           {{ old('aging', $ledger->aging) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="aging">
                                        <span>Enable Aging</span>
                                    </label>
                                </div>
                      
             
                        </div>
                        
                        <div class="col-md-4">
          
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="credit_aging" name="credit_aging"
                                           {{ old('credit_aging', $ledger->credit_aging) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="credit_aging">
                                        <span>Credit Aging</span>
                                    </label>
                                </div>
                         
                            </div>
           
                        
                        <div class="col-md-4">
    
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="iv" name="iv"
                                           {{ old('iv', $ledger->iv) ? 'checked' : '' }} onchange="toggleInventoryFields()">
                                    <label class="form-check-label" for="iv">
                                        <span>Inventory Ledger</span>
                                    </label>
                                </div>
                               
               
                        </div>
                    </div>
                    
                    <!-- Opening Balance Section -->
                    @can('chart_of_accounts.manage_opening_balance')
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="section-title"> Opening Balance</h6>
                            <hr>
                        </div>
                    </div>
                
                    @php
                        $openingDr = $openingBalance ? $openingBalance->dr_amount : 0;
                        $openingCr = $openingBalance ? $openingBalance->cr_amount : 0;
                        $balanceAmount = $openingDr > 0 ? $openingDr : $openingCr;
                        $balanceType = $openingDr > 0 ? 'dr' : 'cr';
                        $currentBalance = $ledger->getCurrentBalance();
                    @endphp
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label for="opening_balance" class="form-label">Amount</label>
                                <input type="number" class="form-control @error('opening_balance') is-invalid @enderror" 
                                       id="opening_balance" name="opening_balance" 
                                       value="{{ old('opening_balance', $balanceAmount) }}" 
                                       step="0.01" min="0" placeholder="0.00">
                                @error('opening_balance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                      
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label for="balance_type" class="form-label">Balance Type</label>
                                <select class="form-select @error('balance_type') is-invalid @enderror" 
                                        id="balance_type" name="balance_type">
                                    <option value="dr" {{ old('balance_type', $balanceType) == 'dr' ? 'selected' : '' }}>Debit</option>
                                    <option value="cr" {{ old('balance_type', $balanceType) == 'cr' ? 'selected' : '' }}>Credit</option>
                                </select>
                                @error('balance_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
              
                            </div>
                        </div>
                       
                    </div>
                          <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" 
                                         >{{ old('notes', $ledger->notes) }}</textarea>
                         
                            </div>
                        </div>
                    @endcan
                    
                    <!-- Inventory Fields -->
                    <div id="inventory-fields" style="{{ $ledger->iv ? '' : 'display: none;' }}">
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6 class="section-title"> Inventory Information</h6>
                                <hr>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="quantity" class="form-label">Opening Quantity</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" 
                                           value="{{ old('quantity', $openingBalance ? $openingBalance->quantity : 0) }}" 
                                           min="0" placeholder="0">
                                  
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="unit_price" class="form-label">Unit Price</label>
                                    <input type="number" class="form-control" id="unit_price" name="unit_price" 
                                           value="{{ old('unit_price', $openingBalance ? $openingBalance->unit_price : 0) }}" 
                                           step="0.01" min="0" placeholder="0.00">
                                   
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="uom_id" class="form-label">Unit of Measure</label>
                                    <select class="form-select" id="uom_id" name="uom_id">
                                        <option value="">-- Select UOM --</option>
                                        @php
                                            $uoms = \App\Models\Uom::orderBy('name')->get();
                                        @endphp
                                        @foreach($uoms as $uom)
                                            <option value="{{ $uom->id }}" 
                                                    {{ old('uom_id', $openingBalance ? $openingBalance->uom_id : '') == $uom->id ? 'selected' : '' }}>
                                                {{ $uom->name }}
                                            </option>
                                        @endforeach
                                    </select>
                             
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">Total Value</label>
                                    <input type="text" class="form-control" id="total_value" readonly 
                                           style="background-color: #f8f9fa; font-weight: bold;" placeholder="0.00">
                                    
                                </div>
                            </div>
                        </div>
                        
                    
                    </div>
                    
                    <!-- Warning about existing transactions -->
                    @if($ledger->hasTransactions())
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle"></i> Important Notice</h6>
                                    <p class="mb-2">This ledger has <strong>{{ $ledger->entryItems->count() }} transaction(s)</strong>.</p>
                                    <ul class="mb-2">
                                        <li>Changes to the group or features may affect existing reports</li>
                                        <li>Opening balance changes will affect the current balance</li>
                                        <li>Code changes may impact transaction references</li>
                                    </ul>
                                    <p class="mb-0 small">Please ensure changes are necessary and won't disrupt existing data.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Update Ledger
                                </button>
                              <a href="{{ route('chart_of_accounts.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                
                                
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel"><i class="fas fa-eye"></i> Changes Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Preview content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="$('#ledgerForm').submit()">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.form-label {
    font-weight: 600;
    color: #495057;
}



.section-title {
    color: #495057;
    font-weight: 600;
    margin-bottom: 0;
}

.feature-card {
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 15px;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}

.feature-card:hover {
    border-color: #28a745;
    background-color: #f0f8f4;
}

.form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

.alert-light {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.alert-info {
    background-color: #e8f4fd;
    border-color: #bee5eb;
    color: #0c5460;
}

#total_value {
    font-weight: bold;
    color: #28a745;
}

.badge {
    font-size: 0.875em;
}
.section-title{
   color: #28a745;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Store original values for reset functionality
    window.originalValues = {
        name: '{{ $ledger->name }}',
        group_id: '{{ $ledger->group_id }}',
        right_code: '{{ $ledger->right_code }}',
        notes: '{{ $ledger->notes }}',
        is_bank: {{ $ledger->type ? 'true' : 'false' }},
        reconciliation: {{ $ledger->reconciliation ? 'true' : 'false' }},
        pa: {{ $ledger->pa ? 'true' : 'false' }},
        aging: {{ $ledger->aging ? 'true' : 'false' }},
        credit_aging: {{ $ledger->credit_aging ? 'true' : 'false' }},
        iv: {{ $ledger->iv ? 'true' : 'false' }},
        opening_balance: '{{ $balanceAmount }}',
        balance_type: '{{ $balanceType }}',
        quantity: '{{ $openingBalance ? $openingBalance->quantity : 0 }}',
        unit_price: '{{ $openingBalance ? $openingBalance->unit_price : 0 }}',
        uom_id: '{{ $openingBalance ? $openingBalance->uom_id : '' }}'
    };
    
    // Update left code when group is selected
    updateLeftCode();
    
    // Auto-check reconciliation for bank accounts
    $('#is_bank').change(function() {
        if ($(this).is(':checked')) {
            $('#reconciliation').prop('checked', true);
        }
    });
    
    // Format right code on blur
    $('#right_code').blur(function() {
        var value = $(this).val();
        if (value) {
            $(this).val(value.padStart(4, '0'));
        }
    });
    
    // Calculate total value for inventory
    $('#quantity, #unit_price').on('input', function() {
        calculateTotalValue();
    });
    
    // Initialize total value calculation
    calculateTotalValue();
    
    // Form validation
    $('#ledgerForm').on('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
    });
    
    // Check for changes before leaving
    window.addEventListener('beforeunload', function(e) {
        if (hasUnsavedChanges()) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});

function updateLeftCode() {
    var selectedOption = $('#group_id option:selected');
    var leftCode = selectedOption.data('code');
    $('#left_code').val(leftCode || '');
}

function toggleInventoryFields() {
    if ($('#iv').is(':checked')) {
        $('#inventory-fields').slideDown();
        calculateTotalValue();
    } else {
        $('#inventory-fields').slideUp();
    }
}

function calculateTotalValue() {
    var quantity = parseFloat($('#quantity').val()) || 0;
    var unitPrice = parseFloat($('#unit_price').val()) || 0;
    var totalValue = quantity * unitPrice;
    $('#total_value').val(totalValue.toFixed(2));
}

function validateForm() {
    var isValid = true;
    
    // Clear previous errors
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    // Validate name
    if (!$('#name').val().trim()) {
        showFieldError('#name', 'Ledger name is required');
        isValid = false;
    }
    
    // Validate group
    if (!$('#group_id').val()) {
        showFieldError('#group_id', 'Please select a group');
        isValid = false;
    }
    
    // Validate right code
    if (!$('#right_code').val().trim()) {
        showFieldError('#right_code', 'Right code is required');
        isValid = false;
    }
    
    // Validate opening balance
    var openingBalance = parseFloat($('#opening_balance').val()) || 0;
    if (openingBalance > 0 && !$('#balance_type').val()) {
        showFieldError('#balance_type', 'Balance type is required when amount is specified');
        isValid = false;
    }
    
    return isValid;
}

function showFieldError(field, message) {
    $(field).addClass('is-invalid');
    $(field).after(`<div class="invalid-feedback">${message}</div>`);
}

function resetToOriginal() {
    if (confirm('Are you sure you want to reset all changes to original values?')) {
        // Reset form fields to original values
        $('#name').val(window.originalValues.name);
        $('#group_id').val(window.originalValues.group_id);
        $('#right_code').val(window.originalValues.right_code);
        $('#notes').val(window.originalValues.notes);
        $('#is_bank').prop('checked', window.originalValues.is_bank);
        $('#reconciliation').prop('checked', window.originalValues.reconciliation);
        $('#pa').prop('checked', window.originalValues.pa);
        $('#aging').prop('checked', window.originalValues.aging);
        $('#credit_aging').prop('checked', window.originalValues.credit_aging);
        $('#iv').prop('checked', window.originalValues.iv);
        $('#opening_balance').val(window.originalValues.opening_balance);
        $('#balance_type').val(window.originalValues.balance_type);
        $('#quantity').val(window.originalValues.quantity);
        $('#unit_price').val(window.originalValues.unit_price);
        $('#uom_id').val(window.originalValues.uom_id);
        
        // Clear validation states
        $('.is-invalid').removeClass('is-invalid');
        $('.is-valid').removeClass('is-valid');
        $('.invalid-feedback').remove();
        
        // Update dependent fields
        updateLeftCode();
        toggleInventoryFields();
        calculateTotalValue();
    }
}

function hasUnsavedChanges() {
    return $('#name').val() !== window.originalValues.name ||
           $('#group_id').val() !== window.originalValues.group_id ||
           $('#right_code').val() !== window.originalValues.right_code ||
           $('#notes').val() !== window.originalValues.notes ||
           $('#is_bank').is(':checked') !== window.originalValues.is_bank ||
           $('#reconciliation').is(':checked') !== window.originalValues.reconciliation ||
           $('#pa').is(':checked') !== window.originalValues.pa ||
           $('#aging').is(':checked') !== window.originalValues.aging ||
           $('#credit_aging').is(':checked') !== window.originalValues.credit_aging ||
           $('#iv').is(':checked') !== window.originalValues.iv ||
           $('#opening_balance').val() !== window.originalValues.opening_balance ||
           $('#balance_type').val() !== window.originalValues.balance_type ||
           $('#quantity').val() !== window.originalValues.quantity ||
           $('#unit_price').val() !== window.originalValues.unit_price ||
           $('#uom_id').val() !== window.originalValues.uom_id;
}

function previewChanges() {
    var changes = getChanges();
    var previewHtml = generatePreviewHtml(changes);
    $('#previewContent').html(previewHtml);
    $('#previewModal').modal('show');
}

function getChanges() {
    var changes = {
        modified: [],
        current: {},
        new: {}
    };
    
    // Check each field for changes
    if ($('#name').val() !== window.originalValues.name) {
        changes.modified.push('Name');
        changes.current.name = window.originalValues.name;
        changes.new.name = $('#name').val();
    }
    
    if ($('#group_id').val() !== window.originalValues.group_id) {
        changes.modified.push('Group');
        changes.current.group = '{{ $ledger->group->name ?? "N/A" }}';
        changes.new.group = $('#group_id option:selected').text();
    }
    
    if ($('#right_code').val() !== window.originalValues.right_code) {
        changes.modified.push('Right Code');
        changes.current.right_code = window.originalValues.right_code;
        changes.new.right_code = $('#right_code').val();
    }
    
    // Add other field checks as needed...
    
    return changes;
}

function generatePreviewHtml(changes) {
    if (changes.modified.length === 0) {
        return '<div class="alert alert-info">No changes detected.</div>';
    }
    
    var html = `
        <div class="alert alert-warning">
            <strong>The following fields will be updated:</strong>
            <ul class="mb-0 mt-2">
    `;
    
    changes.modified.forEach(function(field) {
        html += `<li><strong>${field}</strong></li>`;
    });
    
    html += `
            </ul>
        </div>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Field</th>
                    <th>Current Value</th>
                    <th>New Value</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    Object.keys(changes.current).forEach(function(key) {
        html += `
            <tr>
                <td><strong>${key}</strong></td>
                <td>${changes.current[key] || '<em>Empty</em>'}</td>
                <td>${changes.new[key] || '<em>Empty</em>'}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    
    return html;
}
</script>
@endpush
@endsection