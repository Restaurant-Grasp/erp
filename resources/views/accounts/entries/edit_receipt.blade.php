@extends('layouts.app')

@section('content')

@push('styles')
<style>
  
    .section-header {
        background: #00a551;
        color:white;
    }

    .conditional-section {
        border: 2px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .conditional-header {
        background: linear-gradient(135deg, var(--warning-orange, #00a551) 0%, #00a551 100%);
        color: white;
        padding: 12px 16px;
        font-weight: 600;
        font-size: 14px;
    }

    .conditional-body {
        padding: 20px;
        background: #fefefe;
    }


    .btn-modern {
        border-radius: 8px;
        font-weight: 600;
        padding: 12px 24px;
        font-size: 14px;
        transition: all 0.3s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }


    .form-check-modern {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 37px;
    }
.form-control[readonly] {

        background-color: #e9ecef;
}
    .form-check-modern input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: var(--primary-green);
    }

    .form-check-modern label {
        font-weight: 500;
        margin: 0;
        cursor: pointer;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 0.2rem rgba(0, 165, 81, 0.15);
    }

    .select2-container--default .select2-selection--single {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        height: 46px;
        padding: 6px 12px;
    }

    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: var(--primary-green);
    }

    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
        .modern-card-body {
            padding: 16px;
        }
        
 
        
        .btn-modern {
            width: 100%;
            justify-content: center;
            margin-bottom: 8px;
        }
    }
</style>
@endpush

<div class="page-header">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home me-2"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('accounts.receipt.list') }}">Receipt List</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Receipt</li>
        </ol>
    </nav>
</div>

<form method="POST" action="{{ route('accounts.receipt.update', $entry->id) }}" id="receiptForm" onsubmit="return disableSubmitButton()" novalidate>
    @csrf
    @method('PUT')
    <!-- Main Information Card -->
    <div class="card">
        <div class="card-header">
            Edit Receipt Voucher - {{ $entry->entry_code }}
        </div>
        <div class="card-body">
            <!-- Basic Information -->
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control  @error('date') is-invalid @enderror" 
                           value="{{ old('date', $entry->date->format('Y-m-d')) }}" required>
                            @error('date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
             
                <div class="col-md-4">
                    <label class="form-label">Receipt Mode <span class="text-danger">*</span></label>
                    <select name="payment_mode" class="form-select @error('payment_mode') is-invalid @enderror" id="paymentMode" required>
                        <option value="CASH" {{ old('payment_mode', $entry->payment) == 'CASH' ? 'selected' : '' }}>Cash</option>
                        <option value="CHEQUE" {{ old('payment_mode', $entry->payment) == 'CHEQUE' ? 'selected' : '' }}>Cheque</option>
                        <option value="ONLINE" {{ old('payment_mode', $entry->payment) == 'ONLINE' ? 'selected' : '' }}>Online</option>
                    </select>
                          @error('payment_mode')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                    <div class="col-md-5">
                <label class="form-label">Debit A/C <span class="text-danger">*</span></label>
                    <select name="debit_account" class="form-select @error('debit_account') is-invalid @enderror" required>
                        <option value="">Select Bank/Cash Account</option>
                        @foreach($bankLedgers as $ledger)
                            <option value="{{ $ledger->id }}" {{ old('debit_account', $debitAccount->ledger_id) == $ledger->id ? 'selected' : '' }}>
                                {{ $ledger->left_code }}/{{ $ledger->right_code }} - {{ $ledger->name }}
                            </option>
                        @endforeach

                    </select>
                       @error('debit_account')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
            </div>

            <div class="row g-3 mt-2">

               <div class="col-md-3">
                    <label class="form-label">Entry Code <span class="text-danger">*</span></label>
                    <input type="text" name="entry_code" class="form-control @error('entry_code') is-invalid @enderror" 
                           value="{{ old('entry_code', $entry->entry_code) }}" readonly required>
                                               @error('entry_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            <div class="col-md-3">
                    <label class="form-label">Fund <span class="text-danger">*</span></label>
                    <select name="fund_id" class="form-select @error('fund_id') is-invalid @enderror" required>
                        @foreach($funds as $fund)
                            <option value="{{ $fund->id }}" {{ old('fund_id', $entry->fund_id) == $fund->id ? 'selected' : '' }}>
                                {{ $fund->name }}{{ $fund->code ? ' (' . $fund->code . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                        @error('fund_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Received From <span class="text-danger">*</span></label>
                    <input type="text" name="received_from" class="form-control  @error('received_from') is-invalid @enderror" 
                           value="{{ old('received_from', $entry->paid_to) }}" placeholder="Enter payer name" required>
                                  @error('received_from')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <div class="form-check-modern">
                        <input type="checkbox" class="form-check-input" id="discountCheck" {{ $discountItem ? 'checked' : '' }}>
                        <label>Apply Discount</label>
                    </div>
                </div>
            </div>

            <!-- Discount Section -->
            <div class="row g-3 mt-3" id="discountSection" style="{{ $discountItem ? '' : 'display: none;' }}">
                <div class="col-md-6">
                    <label class="form-label">Discount Account <span class="text-danger">*</span></label>
                    <select name="discount_ledger" class="form-select @error('received_from') is-invalid @enderror">
                        <option value="">Select Discount Account</option>
                        @foreach($creditLedgers as $ledger)
                            @if(substr($ledger->left_code, 0, 1) == '5' || substr($ledger->left_code, 0, 1) == '6')
                                <option value="{{ $ledger->id }}" {{ $discountItem && $discountItem->ledger_id == $ledger->id ? 'selected' : '' }}>
                                    {{ $ledger->left_code }}/{{ $ledger->right_code }} - {{ $ledger->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                      @error('discount_ledger')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Discount Amount <span class="text-danger">*</span></label>
                    <input type="number" name="discount_amount" class="form-control @error('discount_amount') is-invalid @enderror" id="discountAmount"
                           step="0.01" min="0" value="{{ $discountItem ? $discountItem->amount : 0.00 }}">
                               @error('discount_amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
<br>
    <!-- Payment Details (Conditional) -->
    <div class="modern-card conditional-section fade-in" id="chequeDetails" style="{{ $entry->payment == 'CHEQUE' ? '' : 'display: none;' }}">
        <div class="conditional-header">
         
            Cheque Details
        </div>
        <div class="conditional-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Cheque Number</label>
                    <input type="text" name="cheque_no" class="form-control @error('cheque_no') is-invalid @enderror" 
                           value="{{ old('cheque_no', $entry->cheque_no) }}" placeholder="Enter cheque number">
                                     @error('cheque_no')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cheque Date</label>
                    <input type="date" name="cheque_date" class="form-control @error('cheque_date') is-invalid @enderror" 
                           value="{{ old('cheque_date', $entry->cheque_date ? $entry->cheque_date->format('Y-m-d') : '') }}">
                                 @error('cheque_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="modern-card conditional-section fade-in" id="onlineDetails" style="{{ $entry->payment == 'ONLINE' ? '' : 'display: none;' }}">
        <div class="conditional-header">
   
            Online Transaction Details
        </div>
        <div class="conditional-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Transaction Number</label>
                    <input type="text" name="transaction_no" class="form-control @error('transaction_no') is-invalid @enderror" 
                           value="{{ old('transaction_no', $entry->cheque_no ?? '') }}" placeholder="Enter transaction number">
                            @error('transaction_no')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Transaction Date</label>
                    <input type="date" name="transaction_date" class="form-control @error('transaction_date') is-invalid @enderror" 
                           value="{{ old('transaction_date', isset($entry->cheque_date) ? $entry->cheque_date->format('Y-m-d') : '') }}">
                              @error('transaction_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
<br>
    <!-- Receipt Items -->
    <div class="card fade-in">
       <div class="card-header section-header">
            Receipt Details
        </div>
          <div class="cared-body px-4">
        <div class="table">
            <table class="table table-borderless mb-0" id="receiptItemsTable">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 45%">Account</th>
                        <th style="width: 20%">Amount</th>
                        <th style="width: 25%">Details</th>
                        <th style="width: 5%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $itemIndex = 0; @endphp
                    @foreach($creditItems as $item)
                    <tr class="receipt-item">
                        <td>
                            <button type="button" class="btn-icon btn btn-danger btn-sm remove-item">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                        <td>
                            <select name="items[{{ $itemIndex }}][ledger_id]" class="form-select select2 item-ledger" required>
                                <option value="">Select Account</option>
                                @foreach($creditLedgers as $ledger)
                                    <option value="{{ $ledger->id }}" {{ $item->ledger_id == $ledger->id ? 'selected' : '' }}>
                                        {{ $ledger->left_code }}/{{ $ledger->right_code }} - {{ $ledger->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="items[{{ $itemIndex }}][amount]" class="form-control item-amount" 
                                   step="0.01" min="0.01" value="{{ $item->amount }}" placeholder="0.00" required>
                        </td>
                        <td>
                            <input type="text" name="items[{{ $itemIndex }}][details]" class="form-control" 
                                   placeholder="Optional details" value="{{ $item->details }}">
                        </td>
                        <td>
                            @if($loop->first)
                            <button type="button" class="btn-icon btn btn-success btn-sm add-item">
                                <i class="fas fa-plus"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @php $itemIndex++; @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: var(--light-green);">
                        <th colspan="2" class="text-end">Total Amount:</th>
                        <th id="itemsTotal">{{ number_format($entry->cr_total, 2) }}</th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        </div>
    </div>
<br>
    <!-- Narration -->
    <div class="card fade-in">
               <div class="card-header section-header">
      
            Receipt Particulars
        </div>
           <div class="card-body px-4">

  <textarea name="narration" class="form-control @error('narration') is-invalid @enderror" rows="3" 
                  placeholder="Enter receipt description">{{ old('narration', $entry->narration) }}</textarea>

                       @error('narration')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
    </div>
    </div>
<br>
    <!-- Amount in Words -->
    <div class="card fade-in">
              <div class="card-header section-header">
       Amount in Words
        
        </div>
     <div class="amount-display px-5 py-3" id="amountInWords">
            @php
            if (!function_exists('numberToWords')) {
                function numberToWords($amount) {
                    if ($amount == 0) return 'ZERO ONLY';

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

                    return strtoupper($ringgitWords) . ' ONLY';
                }
            }

            $cr_total = $entry->cr_total;
            if ($discountItem && !empty($discountItem->amount)) {
                $cr_total -= $discountItem->amount;
            }
            @endphp
            
            <div style="font-size: 24px; margin-bottom: 8px;">RM {{ number_format($cr_total, 2) }}</div>
            <div style="font-size: 16px; opacity: 0.9;">{{ strtoupper(numberToWords($cr_total)) }} ONLY</div>
        </div>
    </div>
<br>
    <!-- Action Buttons -->
    <div class="d-flex gap-3  mb-4">
           <button type="submit" class="btn btn-modern btn-primary" id="submitBtn">
            <i class="fas fa-save"></i>
            Update Receipt
        </button>
        <a href="{{ route('accounts.receipt.list') }}" class="btn btn-modern btn-secondary">
            <i class="fas fa-times"></i>
            Cancel
        </a>
     
    </div>

    <!-- Hidden template for ledger options -->
    <select id="ledgerTemplate" class="d-none">
        @foreach($creditLedgers as $ledger)
            <option value="{{ $ledger->id }}">
                {{ $ledger->left_code }}@if($ledger->left_code && $ledger->right_code)/@endif{{ $ledger->right_code }} - {{ $ledger->name }}
            </option>
        @endforeach
    </select>
</form>
@endsection
@push('scripts')
<script>
$(document).ready(function() {

    
    let itemIndex = {{ count($creditItems) }};
    
    // Payment mode change handler
    $('#paymentMode').change(function() {
        const mode = $(this).val();
        $('.conditional-section').hide();
        
        if (mode === 'CHEQUE') {
            $('#chequeDetails').show().addClass('fade-in');
            $('#chequeDetails input').prop('required', true);
            $('#onlineDetails input').prop('required', false);
        } else if (mode === 'ONLINE') {
            $('#onlineDetails').show().addClass('fade-in');
            $('#onlineDetails input').prop('required', true);
            $('#chequeDetails input').prop('required', false);
        } else {
            $('#chequeDetails input, #onlineDetails input').prop('required', false);
        }
    });
    
    // Discount checkbox handler
    $('#discountCheck').change(function() {
        if ($(this).is(':checked')) {
            $('#discountSection').show().addClass('fade-in');
            $('select[name="discount_ledger"]').attr('required', true);
            $('input[name="discount_amount"]').attr('required', true);
            
            if ($('#discountAmount').val() === '' || $('#discountAmount').val() === '0') {
                $('#discountAmount').val('0.00');
            }
        } else {
            $('#discountSection').hide();
            $('#discountAmount').val('0.00');
            calculateTotal();
            $('select[name="discount_ledger"]').removeAttr('required');
            $('input[name="discount_amount"]').removeAttr('required');
        }
    });
    
    // Add item handler
    $(document).on('click', '.add-item', function() {
        const ledgerOptions = $('#ledgerTemplate').html();
        const newRow = `
            <tr class="receipt-item fade-in">
                <td>
                    <button type="button" class="btn-icon btn btn-danger btn-sm remove-item">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
                <td>
                    <select name="items[${itemIndex}][ledger_id]" class="form-select item-ledger" required>
                        <option value="">Select Account</option>
                        ${ledgerOptions}
                    </select>
                    <option value="">Select Account</option>
                </td>
                <td>
                    <input type="number" name="items[${itemIndex}][amount]" class="form-control item-amount" 
                           step="0.01" min="0.01" placeholder="0.00" required>
                           <option value="">Select Account</option>
                </td>
                <td>
                    <input type="text" name="items[${itemIndex}][details]" class="form-control" 
                           placeholder="Optional details">
                </td>
                <td></td>
            </tr>
        `;
        $('#receiptItemsTable tbody').append(newRow);
        $('#receiptItemsTable tbody tr:last .item-ledger').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
        itemIndex++;
    });
    
    // Remove item handler
    $(document).on('click', '.remove-item', function() {
        if ($('#receiptItemsTable tbody tr').length > 1) {
            $(this).closest('tr').remove();
            calculateTotal();
        }
    });
    
    // Calculate total on amount change
    $(document).on('input change keyup', '.item-amount, #discountAmount', function() {
        calculateTotal();
    });
    
    // Calculate total function
    function calculateTotal() {
        let total = 0;
        $('.item-amount').each(function() {
            const value = parseFloat($(this).val()) || 0;
            total += value;
        });
        
        let discount = parseFloat($('#discountAmount').val()) || 0;
        let finalTotal = total - discount;
        
        $('#itemsTotal').text(total.toFixed(2));
        updateAmountInWords(finalTotal);
    }
    
    // Update amount in words
    function updateAmountInWords(amount) {
        const words = numberToWords(amount);
        $('#amountInWords').html(`
            <div style="font-size: 24px; margin-bottom: 8px;">RM ${amount.toFixed(2)}</div>
            <div style="font-size: 16px; opacity: 0.9;">${words}</div>
        `);
    }
    
    // Number to words conversion
    function numberToWords(amount) {
        if (amount === 0) return 'ZERO ONLY';
        
        const ones = ['', 'ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE'];
        const teens = ['TEN', 'ELEVEN', 'TWELVE', 'THIRTEEN', 'FOURTEEN', 'FIFTEEN', 'SIXTEEN', 'SEVENTEEN', 'EIGHTEEN', 'NINETEEN'];
        const tens = ['', '', 'TWENTY', 'THIRTY', 'FORTY', 'FIFTY', 'SIXTY', 'SEVENTY', 'EIGHTY', 'NINETY'];
        const thousands = ['', 'THOUSAND', 'MILLION', 'BILLION'];

        function convert_hundreds(num) {
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

        function convert_whole_number(num) {
            let word = '';
            let i = 0;
            while (num > 0) {
                const rem = num % 1000;
                if (rem > 0) {
                    word = convert_hundreds(rem) + ' ' + thousands[i] + ' ' + word;
                }
                num = Math.floor(num / 1000);
                i++;
            }
            return word.trim();
        }

        const ringgit = Math.floor(amount);
        const sen = Math.round((amount % 1) * 100);

        const ringgitWords = convert_whole_number(ringgit);
        const senWords = sen > 0 ? ' AND ' + convert_hundreds(sen) + ' SEN' : '';

        return `RINGGIT ${ringgitWords}${senWords} ONLY`;
    }
    
    // Initialize calculation
    calculateTotal();
});

// Form submission with loading state
function disableSubmitButton() {
    const form = document.getElementById('receiptForm');
    const submitBtn = document.getElementById('submitBtn');

    if (form.checkValidity()) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        return true;
    }
    return false;
}
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
$('#receiptForm').on('submit', function (e) {
    $('.error').text(''); // clear old messages
    let valid = true;

    // Basic required fields
    valid &= requireField($('input[name="date"]'), 'Date is required');
    valid &= requireField($('select[name="payment_mode"]'), 'Payment Mode is required');
    valid &= requireField($('select[name="debit_account"]'), 'Debit Account is required');
    valid &= requireField($('input[name="received_from"]'), 'Received From is required');

    // Conditional fields
    if ($('#paymentMode').val() === 'CHEQUE') {
        valid &= requireField($('input[name="cheque_date"]'), 'Cheque Date is required');
        valid &= requireField($('#chequeDetails input[name="transaction_no"]'), 'Transaction Number is required');
    }
    if ($('#paymentMode').val() === 'ONLINE') {
        valid &= requireField($('#onlineDetails input[name="transaction_no"]'), 'Transaction Number is required');
        valid &= requireField($('input[name="transaction_date"]'), 'Transaction Date is required');
    }

    // Discount fields (if enabled)
    if ($('#discountCheck').is(':checked')) {
        valid &= requireField($('select[name="discount_ledger"]'), 'Discount Account is required');
        const discVal = parseFloat($('#discountAmount').val());
        if (isNaN(discVal) || discVal <= 0) {
            showFieldError($('#discountAmount'), 'Discount Amount must be greater than 0');
            valid = false;
        }
    }

    // Receipt items validation
    const $rows = $('#receiptItemsTable tbody tr.receipt-item');
    if ($rows.length === 0) {
        $('#receiptItemsTable').after('<span class="text-danger error d-block mt-2">Please add at least one item</span>');
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

    // Totals sanity check: discount cannot exceed total items
    const itemsTotal = $('#receiptItemsTable tbody .item-amount').toArray()
        .reduce((sum, el) => sum + (parseFloat($(el).val()) || 0), 0);
    const discount = parseFloat($('#discountAmount').val()) || 0;
    if ($('#discountCheck').is(':checked') && discount > itemsTotal) {
        showFieldError($('#discountAmount'), 'Discount cannot exceed total amount');
        valid = false;
    }

    if (!valid) e.preventDefault();
});

// --- Helpers ---
function requireField($el, msg) {
    if (!$el.val()) {
        showFieldError($el, msg);
        return false;
    }
    return true;
}
function showFieldError($el, msg) {
    const $err = $el.next('.error').length
        ? $el.next('.error')
        : $('<span class="text-danger error"></span>').insertAfter($el);
    $err.text(msg);
}
</script>
@endpush