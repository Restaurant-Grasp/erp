@extends('layouts.app')
@section('title', 'Create Service')

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* Custom Select2 styling to match your theme */
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 6px 12px;
        display: flex;
        align-items: center;
    }

    .select2-selection__placeholder {
        color: black !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 24px;
        padding-left: 0;
        color: #495057;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 10px;
    }

    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 0.2rem rgba(0, 165, 81, .25);
    }

    .select2-dropdown {
        border: 1px solid var(--primary-green);
        border-radius: 0.375rem;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: var(--primary-green);
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 6px 12px;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 0.2rem rgba(0, 165, 81, .25);
    }

    /* Error state styling */
    .is-invalid+.select2-container--default .select2-selection--single {
        border-color: #dc3545;
    }

    .is-invalid+.select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, .25);
    }

    /* Optional field styling */
    .optional-field {
        opacity: .7;
    }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Create Service</h5>
    </div>
    <div class="card-body">
        <form id="serviceForm" method="POST" action="{{ route('service.store') }}" autocomplete="off">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                            value="{{ old('code') }}" placeholder="e.g., SRV001">
                        @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Leave blank for auto-generation</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Service Type <span class="text-danger">*</span></label>
                        <select name="service_type_id"
                            class="form-select service-type-select2 @error('service_type_id') is-invalid @enderror">
                            <option value="">-- Select Service Type --</option>
                            @foreach($serviceTypes as $type)
                            <option value="{{ $type->id }}" {{ old('service_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('service_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Base Price <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" name="base_price"
                                class="form-control @error('base_price') is-invalid @enderror"
                                value="{{ old('base_price') }}" step="0.01" min="0">
                        </div>
                        @error('base_price')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Item Type <span class="text-danger">*</span></label>
                        <select name="item_type" class="form-select @error('item_type') is-invalid @enderror" id="item_type">
                            <option value="">-- Select Item Type --</option>
                            <option value="service" {{ old('item_type') == 'service' ? 'selected' : '' }}>Service</option>
                            <option value="product" {{ old('item_type') == 'product' ? 'selected' : '' }}>Product</option>
                            <option value="item" {{ old('item_type') == 'item' ? 'selected' : '' }}>Item</option>
                        </select>
                        @error('item_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3" id="ledger-field">
                        <label class="form-label mb-2">
                            Revenue Ledger
                            <span class="text-danger required-asterisk">*</span>
                            <span class="text-muted optional-text" style="display:none;">(Optional)</span>
                        </label>
                        <select name="ledger_id" class="form-select ledger-select2 @error('ledger_id') is-invalid @enderror">
                            <option value="">-- Search and Select Revenue Ledger --</option>
                            @foreach($ledgers as $ledger)
                            <option value="{{ $ledger->id }}" {{ old('ledger_id') == $ledger->id ? 'selected' : '' }}>
                                {{ $ledger->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('ledger_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Select the ledger account for revenue posting</small>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Billing Cycle <span class="text-danger">*</span></label>
                        <select name="billing_cycle" class="form-select @error('billing_cycle') is-invalid @enderror" id="billing_cycle">
                            <option value="">-- Select Billing Cycle --</option>
                            <option value="one-time" {{ old('billing_cycle') == 'one-time' ? 'selected' : '' }}>One Time</option>
                            <option value="monthly" {{ old('billing_cycle') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ old('billing_cycle') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="yearly" {{ old('billing_cycle') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                        </select>
                        @error('billing_cycle')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="is_recurring" id="is_recurring"
                                {{ old('is_recurring') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_recurring">Recurring Service</label>
                        </div>
                        <small class="form-text text-muted">Check if this service is recurring</small>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                    rows="3" placeholder="Enter service description">{{ old('description') }}</textarea>
                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="status" id="status" value="1" {{ old('status', 1) ? 'checked' : '' }}>
                    <label class="form-check-label" for="status">Active Status</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Create Service
                </button>
                <a href="{{ route('service.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- jQuery Validate (assumes jQuery already loaded in layout) -->
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/additional-methods.min.js"></script>

<script>
    $(function() {
        // Initialize Select2
        $('.service-type-select2').select2({
            placeholder: '-- Select Service Type --',
            allowClear: true,
            width: '100%'
        });

        $('.ledger-select2').select2({
            placeholder: '-- Search and Select Revenue Ledger --',
            allowClear: true,
            width: '100%'
        });

        // Toggle ledger requirement based on item_type
        function toggleLedgerRequirement() {
            const itemType = $('#item_type').val();
            const $ledgerField = $('#ledger-field');
            const $requiredAsterisk = $ledgerField.find('.required-asterisk');
            const $optionalText = $ledgerField.find('.optional-text');
            const $ledgerSelect = $ledgerField.find('select[name="ledger_id"]');

            if (itemType === 'item') {
                $requiredAsterisk.hide();
                $optionalText.show();
                $ledgerField.removeClass('required-field').addClass('optional-field');
                $ledgerSelect.removeAttr('required');
            } else if (itemType === 'service' || itemType === 'product' || !itemType) {
                $requiredAsterisk.show();
                $optionalText.hide();
                $ledgerField.removeClass('optional-field').addClass('required-field');
                $ledgerSelect.attr('required', 'required');
            }
        }
        toggleLedgerRequirement();

        $('#item_type').on('change', function() {
            toggleLedgerRequirement();
            // revalidate ledger when item_type changes
            $('select[name="ledger_id"]').valid();
        });

        // Auto-enable recurring when certain billing cycles are selected
        $('#billing_cycle').on('change', function() {
            const v = $(this).val();
            const $rec = $('#is_recurring');
            if (v === 'monthly' || v === 'quarterly' || v === 'yearly') {
                $rec.prop('checked', true);
            } else if (v === 'one-time') {
                $rec.prop('checked', false);
            }
        });

        // jQuery Validate
        const $form = $('#serviceForm');

        // Ensure Select2 change events trigger validation
        $('.service-type-select2, .ledger-select2').on('change', function() {
            $(this).valid();
        });

        $form.validate({
            ignore: [], // validate hidden fields too (Select2)
            rules: {
                name: {
                    required: true,
                    maxlength: 255
                },
                code: {
                    maxlength: 50
                },
                service_type_id: {
                    required: true
                },
                item_type: {
                    required: true
                },
                ledger_id: {
                    required: function() {
                        const it = $('#item_type').val();
                        return (it === 'service' || it === 'product' || !it);
                    }
                },
                base_price: {
                    required: true,
                    number: true,
                    min: 0
                },
                billing_cycle: {
                    required: true
                }
            },
            messages: {
                name: {
                    required: "Please enter a service name",
                },
                service_type_id: {
                    required: "Please select a service type"
                },
                item_type: {
                    required: "Please select an item type"
                },
                ledger_id: {
                    required: "Please select a revenue ledger"
                },
                base_price: {
                    required: "Please enter a base price",
                    number: "Base price must be a valid number",
                    min: "Base price cannot be negative"
                },
                billing_cycle: {
                    required: "Please select a billing cycle"
                }
            },
            errorElement: "div",
            errorClass: "invalid-feedback",
            highlight: function(el) {
                const $el = $(el);
                $el.addClass("is-invalid");
                // add class on Select2 container too
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.next('.select2').find('.select2-selection').addClass('is-invalid');
                }
            },
            unhighlight: function(el) {
                const $el = $(el);
                $el.removeClass("is-invalid");
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.next('.select2').find('.select2-selection').removeClass('is-invalid');
                }
            },
            errorPlacement: function(error, element) {
                if (element.hasClass('select2-hidden-accessible')) {
                    error.insertAfter(element.next('.select2')); // after Select2
                } else if (element.closest('.input-group').length) {
                    error.insertAfter(element.closest('.input-group'));
                } else {
                    error.insertAfter(element);
                }
            }
        });
    });
</script>
@endpush