@extends('layouts.app')
@section('title', 'Create Ledger')

@section('content')
<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb pl-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('chart_of_accounts.index') }}">Chart of Accounts</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create Ledger</li>
            </ol>
        </nav>
        <br>
        <div class="card">
            <div class="card-header">
                <h5> Create New Ledger</h5>
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

                <form method="POST" action="{{ route('chart_of_accounts.ledger.store') }}" id="ledgerForm">
                    @csrf

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
                                    id="name" name="name" value="{{ old('name') }}" required
                                    placeholder="Enter ledger name">
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Enter a descriptive name for the ledger account</div>
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
                                        {{ old('group_id') == $group->id ? 'selected' : '' }}>
                                        {!! $group->display_name !!}
                                    </option>
                                    @endforeach
                                </select>
                                @error('group_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Select the account group this ledger belongs to</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Ledger Code</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="left_code"
                                        placeholder="Group Code" readonly style="max-width: 120px; background-color: #f8f9fa;">
                                    <span class="input-group-text">/</span>
                                    <input type="text" class="form-control @error('right_code') is-invalid @enderror"
                                        id="right_code" name="right_code" placeholder="Enter code"
                                        value="{{ old('right_code') }}" maxlength="4" required>
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
                            <h6 class="section-title"> Ledger Features</h6>
                            <hr>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">

                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_bank" name="is_bank"
                                    {{ old('is_bank') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_bank">
                                    <span>Bank/Cash Account</span>
                                </label>
                            </div>


                        </div>

                        <div class="col-md-4">

                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="reconciliation" name="reconciliation"
                                    {{ old('reconciliation') ? 'checked' : '' }}>
                                <label class="form-check-label" for="reconciliation">
                                    <span>Enable Reconciliation</span>
                                </label>
                            </div>


                        </div>

                        <div class="col-md-4">

                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="pa" name="pa"
                                    {{ old('pa') ? 'checked' : '' }}>
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
                                    {{ old('aging') ? 'checked' : '' }}>
                                <label class="form-check-label" for="aging">
                                    <span>Enable Aging</span>
                                </label>


                            </div>
                        </div>

                        <div class="col-md-4">

                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="credit_aging" name="credit_aging"
                                    {{ old('credit_aging') ? 'checked' : '' }}>
                                <label class="form-check-label" for="credit_aging">
                                    <span>Credit Aging</span>
                                </label>
                            </div>


                        </div>

                        <div class="col-md-4">

                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="iv" name="iv"
                                    {{ old('iv') ? 'checked' : '' }} onchange="toggleInventoryFields()">
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


                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="opening_balance" class="form-label">Amount</label>
                                <input type="number" class="form-control @error('opening_balance') is-invalid @enderror"
                                    id="opening_balance" name="opening_balance" value="{{ old('opening_balance', 0) }}"
                                    step="0.01" min="0" placeholder="0.00">
                                @error('opening_balance')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="balance_type" class="form-label">Balance Type</label>
                                <select class="form-select @error('balance_type') is-invalid @enderror"
                                    id="balance_type" name="balance_type">
                                    <option value="dr" {{ old('balance_type') == 'dr' ? 'selected' : '' }}>Debit</option>
                                    <option value="cr" {{ old('balance_type') == 'cr' ? 'selected' : '' }}>Credit</option>
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
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>

                        </div>
                    </div>
                    @endcan

                    <!-- Inventory Fields -->
                    <div id="inventory-fields" style="display: none;">
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6 class="section-title">Inventory Information</h6>
                                <hr>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="quantity" class="form-label">Opening Quantity</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity"
                                        value="{{ old('quantity', 0) }}" min="0" placeholder="0">
                                   
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="unit_price" class="form-label">Unit Price</label>
                                    <input type="number" class="form-control" id="unit_price" name="unit_price"
                                        value="{{ old('unit_price', 0) }}" step="0.01" min="0" placeholder="0.00">
                               
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
                                        <option value="{{ $uom->id }}" {{ old('uom_id') == $uom->id ? 'selected' : '' }}>
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
                                        style="background-color: #f8f9fa;" placeholder="0.00">
                                   
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Ledger
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
                <h5 class="modal-title" id="previewModalLabel"><i class="fas fa-eye"></i> Ledger Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Preview content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="$('#ledgerForm').submit()">
                    <i class="fas fa-save"></i> Create This Ledger
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
        border-color: #00A551;
        background-color: #f0f8f4;
    }

    .form-check-input:checked {
        background-color: #00A551;
        border-color: #00A551;
    }

    .btn-primary {
        background-color: #00A551;
        border-color: #00A551;
    }

    .btn-primary:hover {
        background-color: #008741;
        border-color: #008741;
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
    .section-title{
                color: #28a745;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
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

        // Form validation
        $('#ledgerForm').on('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
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
            $('#quantity').val(0);
            $('#unit_price').val(0);
            $('#uom_id').val('');
            $('#total_value').val('0.00');
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

    function previewLedger() {
        var formData = {
            name: $('#name').val(),
            group: $('#group_id option:selected').text(),
            left_code: $('#left_code').val(),
            right_code: $('#right_code').val(),
            notes: $('#notes').val(),
            features: getSelectedFeatures(),
            opening_balance: $('#opening_balance').val(),
            balance_type: $('#balance_type option:selected').text(),
            inventory: getInventoryData()
        };

        var previewHtml = generatePreviewHtml(formData);
        $('#previewContent').html(previewHtml);
        $('#previewModal').modal('show');
    }

    function getSelectedFeatures() {
        var features = [];
        if ($('#is_bank').is(':checked')) features.push('Bank/Cash Account');
        if ($('#reconciliation').is(':checked')) features.push('Reconciliation');
        if ($('#pa').is(':checked')) features.push('P&L Accumulation');
        if ($('#aging').is(':checked')) features.push('Aging');
        if ($('#credit_aging').is(':checked')) features.push('Credit Aging');
        if ($('#iv').is(':checked')) features.push('Inventory');
        return features;
    }

    function getInventoryData() {
        if (!$('#iv').is(':checked')) return null;

        return {
            quantity: $('#quantity').val(),
            unit_price: $('#unit_price').val(),
            uom: $('#uom_id option:selected').text(),
            total_value: $('#total_value').val()
        };
    }

    function generatePreviewHtml(data) {
        var html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Basic Information</h6>
                <table class="table table-sm">
                    <tr><th>Name:</th><td>${data.name || 'Not specified'}</td></tr>
                    <tr><th>Group:</th><td>${data.group || 'Not selected'}</td></tr>
                    <tr><th>Code:</th><td>${data.left_code}/${data.right_code}</td></tr>
                    <tr><th>Notes:</th><td>${data.notes || 'None'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Features</h6>
                <div class="mb-3">
                    ${data.features.length > 0 ? data.features.map(f => `<span class="badge bg-success me-1">${f}</span>`).join('') : '<span class="text-muted">No special features</span>'}
                </div>
                
                <h6>Opening Balance</h6>
                <table class="table table-sm">
                    <tr><th>Amount:</th><td>RM ${data.opening_balance || '0.00'}</td></tr>
                    <tr><th>Type:</th><td>${data.balance_type || 'Debit'}</td></tr>
                </table>
            </div>
        </div>
    `;

        if (data.inventory) {
            html += `
            <div class="row mt-3">
                <div class="col-md-12">
                    <h6>Inventory Information</h6>
                    <table class="table table-sm">
                        <tr><th>Quantity:</th><td>${data.inventory.quantity}</td></tr>
                        <tr><th>Unit Price:</th><td>RM ${data.inventory.unit_price}</td></tr>
                        <tr><th>UOM:</th><td>${data.inventory.uom}</td></tr>
                        <tr><th>Total Value:</th><td>RM ${data.inventory.total_value}</td></tr>
                    </table>
                </div>
            </div>
        `;
        }

        return html;
    }

    // Reset form
    $('button[type="reset"]').click(function() {
        $('.is-invalid').removeClass('is-invalid');
        $('.is-valid').removeClass('is-valid');
        $('.invalid-feedback').remove();
        $('#inventory-fields').hide();
        $('#left_code').val('');
        $('#total_value').val('0.00');
    });
</script>
@endpush
@endsection