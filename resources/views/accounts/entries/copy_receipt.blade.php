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
            <li class="breadcrumb-item active" aria-current="page">Copy Receipt</li>
        </ol>
    </nav>
</div>

<form method="POST" action="{{ route('accounts.receipt.store') }}" id="receiptForm" onsubmit="return disableSubmitButton()">
    @csrf

  


    <!-- Main Information Card -->
    <div class="card fade-in">
        <div class="card-header">
            Copy Receipt Voucher - From {{ $sourceEntry->entry_code }}
        </div>
        <div class="card-body">
            <!-- Basic Information -->
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control" 
                           value="{{ old('date', date('Y-m-d')) }}" required>
                   
                </div>
                <div class="col-md-4">
                     <label class="form-label">Receipt Mode <span class="text-danger">*</span></label>
                    <select name="payment_mode" class="form-select" id="paymentMode" required>
                        <option value="CASH" {{ old('payment_mode', $sourceEntry->payment) == 'CASH' ? 'selected' : '' }}>Cash</option>
                        <option value="CHEQUE" {{ old('payment_mode', $sourceEntry->payment) == 'CHEQUE' ? 'selected' : '' }}>Cheque</option>
                        <option value="ONLINE" {{ old('payment_mode', $sourceEntry->payment) == 'ONLINE' ? 'selected' : '' }}>Online</option>
                    </select>
                  
                 
                </div>
                <div class="col-md-5">
                   <label class="form-label">Debit Account <span class="text-danger">*</span></label>
                    <select name="debit_account" class="form-select select2" required>
                        <option value="">Select Bank/Cash Account</option>
                        @foreach($bankLedgers as $ledger)
                            <option value="{{ $ledger->id }}" {{ old('debit_account', $debitAccount->ledger_id) == $ledger->id ? 'selected' : '' }}>
                                {{ $ledger->left_code }}/{{ $ledger->right_code }} - {{ $ledger->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
               
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-3">
                      <label class="form-label">Entry Code <span class="text-danger">*</span></label>
                    <input type="text" name="entry_code" class="form-control" 
                           value="{{ old('entry_code', $entryCode) }}" readonly required>
                </div>
                <div class="col-md-3">
                     <label class="form-label">Fund <span class="text-danger">*</span></label>
                    <select name="fund_id" class="form-select select2" required>
                        @foreach($funds as $fund)
                            <option value="{{ $fund->id }}" {{ old('fund_id', $sourceEntry->fund_id) == $fund->id ? 'selected' : '' }}>
                                {{ $fund->name }}{{ $fund->code ? ' (' . $fund->code . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                  
                </div>
                <div class="col-md-3">
                      <label class="form-label">Received From <span class="text-danger">*</span></label>
                    <input type="text" name="received_from" class="form-control" 
                           value="{{ old('received_from', $sourceEntry->paid_to) }}" placeholder="Enter payer name" required>
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
                    <select name="discount_ledger" class="form-select select2">
                        <option value="">Select Discount Account</option>
                        @foreach($creditLedgers as $ledger)
                            @if(substr($ledger->left_code, 0, 1) == '5' || substr($ledger->left_code, 0, 1) == '6')
                                <option value="{{ $ledger->id }}" {{ $discountItem && $discountItem->ledger_id == $ledger->id ? 'selected' : '' }}>
                                    {{ $ledger->left_code }}/{{ $ledger->right_code }} - {{ $ledger->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Discount Amount <span class="text-danger">*</span></label>
                    <input type="number" name="discount_amount" class="form-control" id="discountAmount"
                           step="0.01" min="0" value="{{ $discountItem ? $discountItem->amount : 0.00 }}">
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Details (Conditional) -->
    <div class="modern-card conditional-section fade-in" id="chequeDetails" style="{{ $sourceEntry->payment == 'CHEQUE' ? '' : 'display: none;' }}">
        <div class="conditional-header">
            Cheque Details
        </div>
        <div class="conditional-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Cheque Number</label>
                    <input type="text" name="cheque_no" class="form-control" 
                           value="{{ old('cheque_no', $sourceEntry->cheque_no) }}" placeholder="Enter cheque number">
                    @if($sourceEntry->cheque_no)
                    <div class="source-data">
                        <strong>Original:</strong> {{ $sourceEntry->cheque_no }}
                    </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cheque Date</label>
                    <input type="date" name="cheque_date" class="form-control" 
                           value="{{ old('cheque_date', isset($sourceEntry->cheque_date) ? $sourceEntry->cheque_date->format('Y-m-d') : '') }}">
                    @if($sourceEntry->cheque_date)
                    <div class="source-data">
                        <strong>Original:</strong> {{ $sourceEntry->cheque_date->format('d-m-Y') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modern-card conditional-section fade-in" id="onlineDetails" style="{{ $sourceEntry->payment == 'ONLINE' ? '' : 'display: none;' }}">
        <div class="conditional-header">
            Online Transaction Details
        </div>
        <div class="conditional-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Transaction Number</label>
                    <input type="text" name="transaction_no" class="form-control" 
                           value="{{ old('transaction_no', $sourceEntry->cheque_no) }}" placeholder="Enter transaction number">
                    @if($sourceEntry->cheque_no)
                    <div class="source-data">
                        <strong>Original:</strong> {{ $sourceEntry->cheque_no }}
                    </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <label class="form-label">Transaction Date</label>
                    <input type="date" name="transaction_date" class="form-control" 
                           value="{{ old('transaction_date', isset($sourceEntry->cheque_date) ? $sourceEntry->cheque_date->format('Y-m-d') : '') }}">
                    @if($sourceEntry->cheque_date)
                    <div class="source-data">
                        <strong>Original:</strong> {{ $sourceEntry->cheque_date->format('d-m-Y') }}
                    </div>
                    @endif
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
        <div class="modern-table">
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
                        <th id="itemsTotal">{{ number_format($sourceEntry->cr_total, 2) }}</th>
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
        <textarea name="narration" class="form-control" rows="3" 
                  placeholder="Enter receipt description">{{ old('narration', $sourceEntry->narration) }}</textarea>
        @if($sourceEntry->narration)
        <div class="source-data mt-2">
            <strong>Original Narration:</strong> {{ $sourceEntry->narration }}
        </div>
        @endif
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

            $cr_total = $sourceEntry->cr_total;
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
    <div class="d-flex gap-3 mb-4">
          <button type="submit" class="btn btn-modern btn-primary" id="submitBtn">
            <i class="fas fa-save"></i>
            Save New Receipt
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
                </td>
                <td>
                    <input type="number" name="items[${itemIndex}][amount]" class="form-control item-amount" 
                           step="0.01" min="0.01" placeholder="0.00" required>
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
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Copy...';
        return true;
    }
    return false;
}
</script>
@endpush