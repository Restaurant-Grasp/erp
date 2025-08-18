@extends('layouts.app')
@section('title', 'Add Payment Voucher')
@section('content')

<div class="row">
    <div class="col-12">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('accounts.payment.list') }}">Payment List</a></li>
                <li class="breadcrumb-item active">Add Payment</li>
            </ol>
        </nav>

        <form method="POST" action="{{ route('accounts.payment.store') }}" id="paymentForm" onsubmit="return validateAndSubmit()" novalidate>
            @csrf
            
            <!-- Header Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                   
                        <h5 class="mb-0">Add Payment Voucher</h5>
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
                    
                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" 
                                       value="{{ old('date', date('Y-m-d')) }}" required>
                                        @error('date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Entry Code <span class="text-danger">*</span></label>
                                <input type="text" name="entry_code" class="form-control @error('entry_code') is-invalid @enderror" readonly
                                       value="{{ old('entry_code', $entryCode) }}" required>
                                        @error('entry_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Fund <span class="text-danger">*</span></label>
                                <select name="fund_id" class="form-control @error('fund_id') is-invalid @enderror" required>
                                    @foreach($funds as $fund)
                                        <option value="{{ $fund->id }}" {{ old('fund_id', 1) == $fund->id ? 'selected' : '' }}>
                                            {{ $fund->name }}{{ $fund->code ? ' (' . $fund->code . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                           @error('fund_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                                <select name="payment_mode" class="form-control @error('payment_mode') is-invalid @enderror" id="paymentMode" required>
                                    <option value="CASH" {{ old('payment_mode') == 'CASH' ? 'selected' : '' }}>Cash</option>
                                    <option value="CHEQUE" {{ old('payment_mode') == 'CHEQUE' ? 'selected' : '' }}>Cheque</option>
                                    <option value="ONLINE" {{ old('payment_mode') == 'ONLINE' ? 'selected' : '' }}>Online Transfer</option>
                                </select>
                                     @error('payment_mode')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Bank/Cash Account <span class="text-danger">*</span></label>
                                <select name="credit_account" class="form-control @error('credit_account') is-invalid @enderror" required>
                                    <option value="">Select Account</option>
                                    @foreach($bankLedgers as $ledger)
                                        <option value="{{ $ledger->id }}" {{ old('credit_account') == $ledger->id ? 'selected' : '' }}>
                                            {{ $ledger->left_code }}/{{ $ledger->right_code }} - {{ $ledger->name }}
                                        </option>
                                    @endforeach
                                </select>
                                     @error('credit_account')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Paid To <span class="text-danger">*</span></label>
                                <input type="text" name="paid_to" class="form-control  @error('paid_to') is-invalid @enderror" 
                                       value="{{ old('paid_to') }}" required placeholder="Enter payee name">
                                                 @error('paid_to')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Discount Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="discountCheck">
                                <label class="form-check-label" for="discountCheck">
                                   </i>Apply Discount
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row" id="discountSection" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Discount Account <span class="text-danger">*</span></label>
                                <select name="discount_ledger" class="form-control @error('discount_ledger') is-invalid @enderror">
                                    <option value="">Select Discount Account</option>
                                    @foreach($debitLedgers as $ledger)
                                        @if(substr($ledger->left_code, 0, 1) == '4' || substr($ledger->left_code, 0, 1) == '8')
                                            <option value="{{ $ledger->id }}">
                                                {{ $ledger->left_code }}/{{ $ledger->right_code }} - {{ $ledger->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                 @error('discount_ledger')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Discount Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" name="discount_amount" class="form-control @error('discount_amount') is-invalid @enderror" id="discountAmount"
                                           step="0.01" min="0" placeholder="0.00">
                                                 @error('discount_amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Method Details -->
            <div class="card mb-3" id="chequeDetails" style="display: none;">
                <div class="card-header bg-warning">
                    <h6 class="mb-0 text-white"><i class="fas fa-money-check me-2"></i>Cheque Details</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Cheque Number</label>
                                <input type="text" name="cheque_no" class="form-control" 
                                       value="{{ old('cheque_no') }}" placeholder="Enter cheque number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Cheque Date</label>
                                <input type="date" name="cheque_date" class="form-control  @error('cheque_date') is-invalid @enderror" 
                                       value="{{ old('cheque_date') }}">
                                        @error('cheque_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-3" id="onlineDetails" style="display: none;">
                <div class="card-header bg-info">
                    <h6 class="mb-0 text-white"><i class="fas fa-globe me-2"></i>Online Transfer Details</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Transaction Number</label>
                                <input type="text" name="transaction_no" class="form-control @error('transaction_no') is-invalid @enderror" 
                                       value="{{ old('transaction_no') }}" placeholder="Enter transaction number">
                                          @error('transaction_no')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Transaction Date</label>
                                <input type="date" name="transaction_date" class="form-control @error('transaction_date') is-invalid @enderror" 
                                       value="{{ old('transaction_date') }}">
                                          @error('transaction_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Items -->
            <div class="card mb-3">
                <div class="card-header bg-primary">
                    <h6 class="mb-0 text-white">Payment Items</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="paymentItemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="40%">Account <span class="text-danger">*</span></th>
                                    <th width="20%">Amount (RM) <span class="text-danger">*</span></th>
                                    <th width="30%">Details</th>
                                    <th width="10%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="payment-item">
                                    <td>
                                        <select name="items[0][ledger_id]" class="form-control item-ledger" required>
                                            <option value="">Select Account</option>
                                            @foreach($debitLedgers as $ledger)
                                                <option value="{{ $ledger->id }}">
                                                    {{ $ledger->left_code }}/{{ $ledger->right_code }} - {{ $ledger->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="number" name="items[0][amount]" class="form-control item-amount" 
                                                   step="0.01" min="0.01" required placeholder="0.00">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][details]" class="form-control" 
                                               placeholder="Optional details">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-success add-item" title="Add Row">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <th class="text-end">Total Amount:</th>
                                    <th><span id="itemsTotal" class="fw-bold">RM 0.00</span></th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Description -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Payment Description</h6>
                </div>
                <div class="card-body">
                    <textarea name="narration" class="form-control @error('narration') is-invalid @enderror" rows="3" 
                              placeholder="Enter payment description or purpose">{{ old('narration') }}</textarea>
                              @error('narration')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
                </div>
            </div>
            
            <!-- Amount in Words -->
            <div class="card mb-4">
                <div class="card-header bg-secondary">
                    <h6 class="mb-0 text-white">Amount in Words</h6>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded">
                        <h5 id="amountInWords" class="mb-0 text-primary">RM 0.00<br><small>ZERO ONLY</small></h5>
                    </div>
                </div>
            </div>
            
            <!-- Submit Buttons -->
    
                    <div class="d-flex gap-3  mb-4">
                           <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save me-1"></i>Save Payment
                        </button>
                        <a href="{{ route('accounts.payment.list') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                     
                    </div>
              
            
            <!-- Hidden template for ledger options -->
            <select id="ledgerTemplateDebit" class="d-none">
                @foreach($debitLedgers as $ledger)
                    <option value="{{ $ledger->id }}">
                        {{ $ledger->left_code }}@if($ledger->left_code && $ledger->right_code)/@endif{{ $ledger->right_code }} - {{ $ledger->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let itemIndex = 0;
    
    // Payment mode change handler
    $('#paymentMode').change(function() {
        const mode = $(this).val();
        
        // Hide all detail sections first
        $('#chequeDetails, #onlineDetails').hide().find('input').prop('required', false);
        
        // Show relevant section
        if (mode === 'CHEQUE') {
            $('#chequeDetails').show().find('input').prop('required', true);
        } else if (mode === 'ONLINE') {
            $('#onlineDetails').show().find('input').prop('required', true);
        }
    });
    
    // Discount checkbox handler
    $('#discountCheck').change(function() {
        if ($(this).is(':checked')) {
            $('#discountSection').slideDown();
            $('select[name="discount_ledger"], input[name="discount_amount"]').attr('required', true);
            if ($('#discountAmount').val() === '' || $('#discountAmount').val() === '0') {
                $('#discountAmount').val('0.00');
            }
        } else {
            $('#discountSection').slideUp();
            $('select[name="discount_ledger"], input[name="discount_amount"]').removeAttr('required');
            $('#discountAmount').val('0.00');
            calculateTotal();
        }
    });
    
    // Add payment item
    $(document).on('click', '.add-item', function() {
        itemIndex++;
        const ledgerOptions = $('#ledgerTemplateDebit').html();
        const newRow = `
            <tr class="payment-item">
                <td>
                    <select name="items[${itemIndex}][ledger_id]" class="form-control item-ledger" required>
                        <option value="">Select Account</option>
                        ${ledgerOptions}
                    </select>
                    <option value="">Select Account</option>
                </td>
                <td>
                    <div class="input-group">
                        <input type="number" name="items[${itemIndex}][amount]" class="form-control item-amount" 
                               step="0.01" min="0.01" required placeholder="0.00">
                               <option value="">Select Account</option>
                    </div>
                </td>
                <td>
                    <input type="text" name="items[${itemIndex}][details]" class="form-control" 
                           placeholder="Optional details">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove-item" title="Remove Row">
                        <i class="fas fa-minus"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#paymentItemsTable tbody').append(newRow);
        
        // Update add button to remove button for previous row
        $(this).removeClass('btn-success add-item').addClass('btn-danger remove-item')
               .html('<i class="fas fa-minus"></i>').attr('title', 'Remove Row');
    });
    
    // Remove payment item
    $(document).on('click', '.remove-item', function() {
        if ($('#paymentItemsTable tbody tr').length > 1) {
            $(this).closest('tr').remove();
            calculateTotal();
        }
    });
    
    // Calculate total when amounts change
    $(document).on('change keyup', '.item-amount, #discountAmount', function() {
        calculateTotal();
    });
    
    function calculateTotal() {
        let total = 0;
        $('.item-amount').each(function() {
            const value = parseFloat($(this).val()) || 0;
            total += value;
        });
        
        const discount = parseFloat($('#discountAmount').val()) || 0;
        const finalTotal = total - discount;
        
        $('#itemsTotal').text('RM ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        updateAmountInWords(finalTotal);
    }
    
    function updateAmountInWords(amount) {
        const words = numberToWords(amount);
        $('#amountInWords').html(`RM ${amount.toLocaleString('en-US', {minimumFractionDigits: 2})}<br><small>${words}</small>`);
    }
    
    function numberToWords(amount) {
        if (amount === 0) return 'ZERO ONLY';
        
        const ones = ["", "ONE", "TWO", "THREE", "FOUR", "FIVE", "SIX", "SEVEN", "EIGHT", "NINE"];
        const teens = ["TEN", "ELEVEN", "TWELVE", "THIRTEEN", "FOURTEEN", "FIFTEEN", "SIXTEEN", "SEVENTEEN", "EIGHTEEN", "NINETEEN"];
        const tens = ["", "", "TWENTY", "THIRTY", "FORTY", "FIFTY", "SIXTY", "SEVENTY", "EIGHTY", "NINETY"];
        const thousands = ["", "THOUSAND", "MILLION", "BILLION"];

        function convertHundreds(num) {
            let str = '';
            if (num > 99) {
                str += ones[Math.floor(num / 100)] + ' HUNDRED ';
                num %= 100;
            }
            if (num > 9 && num < 20) {
                str += teens[num - 10] + ' ';
            } else {
                if (num >= 20) {
                    str += tens[Math.floor(num / 10)] + ' ';
                }
                if (num % 10 > 0) {
                    str += ones[num % 10] + ' ';
                }
            }
            return str.trim();
        }

        function convertWholeNumber(num) {
            let word = '';
            let i = 0;
            while (num > 0) {
                const rem = num % 1000;
                if (rem > 0) {
                    word = convertHundreds(rem) + ' ' + thousands[i] + ' ' + word;
                }
                num = Math.floor(num / 1000);
                i++;
            }
            return word.trim();
        }

        const ringgit = Math.floor(amount);
        const sen = Math.round((amount % 1) * 100);
        const ringgitWords = convertWholeNumber(ringgit);
        const senWords = sen > 0 ? ' AND ' + convertHundreds(sen) + ' SEN' : '';
        
        return `RINGGIT ${ringgitWords}${senWords} ONLY`;
    }
    
    // Form validation and submission
    window.validateAndSubmit = function() {
        const form = document.getElementById('paymentForm');
        const submitBtn = document.getElementById('submitBtn');

        if (form.checkValidity()) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
            return true;
        }
        return false;
    };
    
    // Discount amount focus/blur handlers
    $('#discountAmount').on('focus', function() {
        if (this.value === '0.00') {
            this.value = '';
        }
    }).on('blur', function() {
        if (this.value === '') {
            this.value = '0.00';
        }
    });
    
    // Initialize tooltips
    $('[title]').tooltip();
});
$('#discountCheck').change(function() {
        if ($(this).is(':checked')) {
            $('#discountSection').show();
            $('select[name="discount_ledger"]').attr('required', true);
            $('input[name="discount_amount"]').attr('required', true);
        } else {
            $('#discountSection').hide();
            $('select[name="discount_ledger"]').removeAttr('required');
            $('input[name="discount_amount"]').removeAttr('required');
            // Clear discount field errors
            $('select[name="discount_ledger"], input[name="discount_amount"]').next('.error').remove();
        }
    });

      $(document).on('input', 'input', function() {
        $(this).next('.error').remove();
    });
    $(document).on('change', 'select', function() {
        $(this).next('.error').remove();
    });
// Clear inline errors when user edits fields
$(document).on('input', 'input', function () { clearFieldError($(this)); });
$(document).on('change', 'select', function () { clearFieldError($(this)); });

$('#paymentForm').on('submit', function (e) {
  $('.error').remove(); // remove previous inline error spans
  let valid = true;

  // ---- Basic required fields ----
  valid = requireField($('input[name="date"]'), 'Date is required') && valid;
  valid = requireField($('input[name="entry_code"]'), 'Entry Code is required') && valid;
  valid = requireField($('select[name="fund_id"]'), 'Fund is required') && valid;            // FIX: select, not input
  valid = requireField($('select[name="payment_mode"]'), 'Payment Mode is required') && valid;
  valid = requireField($('select[name="credit_account"]'), 'Bank/Cash Account is required') && valid;
  valid = requireField($('input[name="paid_to"]'), 'Paid To is required') && valid;

  // ---- Mode-specific fields ----
  const mode = $('#paymentMode').val();
  if (mode === 'CHEQUE') {
    // FIX: cheque requires cheque_no + cheque_date (not transaction_no)
    valid = requireField($('input[name="cheque_no"]'), 'Cheque Number is required') && valid;
    valid = requireField($('input[name="cheque_date"]'), 'Cheque Date is required') && valid;
  } else if (mode === 'ONLINE') {
    valid = requireField($('#onlineDetails input[name="transaction_no"]'), 'Transaction Number is required') && valid;
    valid = requireField($('input[name="transaction_date"]'), 'Transaction Date is required') && valid;
  }

  // ---- Discount (when enabled) ----
  const discountOn = $('#discountCheck').is(':checked');
  const $discountLedger = $('select[name="discount_ledger"]');
  const $discountAmount = $('#discountAmount');
  let discount = 0;

  if (discountOn) {
    valid = requireField($discountLedger, 'Discount Account is required') && valid;

    discount = parseFloat($discountAmount.val());
    if (isNaN(discount) || discount <= 0) {
      showFieldError($discountAmount, 'Discount Amount must be greater than 0');
      valid = false;
    }
  }

  // ---- Payment items ----
  const $rows = $('#paymentItemsTable tbody tr.payment-item');
  if ($rows.length === 0) {
    $('#paymentItemsTable').after('<span class="text-danger error d-block mt-2">Please add at least one item</span>');
    valid = false;
  } else {
    $rows.each(function () {
      const $ledger = $(this).find('.item-ledger');
      const $amount = $(this).find('.item-amount');

      if (!$ledger.val()) {
        showFieldError($ledger, 'Account is required');
        valid = false;
      }

      const amt = parseFloat($amount.val());
      if (isNaN(amt) || amt <= 0) {
        showFieldError($amount, 'Amount must be greater than 0');
        valid = false;
      }
    });
  }

  // ---- Totals sanity: discount cannot exceed items total ----
  const itemsTotal = $('#paymentItemsTable tbody .item-amount').toArray()
    .reduce((sum, el) => sum + (parseFloat($(el).val()) || 0), 0);

  if (discountOn && discount > itemsTotal) {
    showFieldError($discountAmount, 'Discount cannot exceed total amount');
    valid = false;
  }

  if (!valid) e.preventDefault();
});

// --- Helpers ---
function requireField($el, msg) {
  // Works for input/select/textarea; treats empty string as invalid
  const val = ($el.val() || '').toString().trim();
  if (!val) {
    showFieldError($el, msg);
    return false;
  }
  return true;
}

function showFieldError($el, msg) {
  const $err = $('<span class="text-danger error"></span>').text(msg);
  // If there's an input-group, place error after the group; else after element
  if ($el.closest('.input-group').length) {
    $el.closest('.input-group').after($err);
  } else {
    $el.after($err);
  }
}

function clearFieldError($el) {
  // Remove only the error directly following this field or its input-group
  const $group = $el.closest('.input-group');
  if ($group.length) {
    const $next = $group.next('.error');
    if ($next.length) $next.remove();
  } else {
    const $next = $el.next('.error');
    if ($next.length) $next.remove();
  }
}


</script>
@endpush
@endsection