@extends('layouts.app')
@section('title', 'Copy Payment Voucher')
@section('content')

<div class="row">
    <div class="col-12">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('accounts.payment.list') }}">Payment List</a></li>
                <li class="breadcrumb-item active">Copy Payment</li>
            </ol>
        </nav>

        <form method="POST" action="{{ route('accounts.payment.store') }}" id="paymentForm" onsubmit="return validateAndSubmit()">
            @csrf
            
            <!-- Header Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-copy text-success me-2"></i>
                        <h5 class="mb-0">Copy Payment Voucher</h5>
                        <span class="badge bg-secondary ms-3">From: {{ $sourceEntry->entry_code }}</span>
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

                    <!-- Source Entry Info -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <strong><i class="fas fa-file-invoice me-1"></i>Original Entry:</strong><br>
                                <code>{{ $sourceEntry->entry_code }}</code>
                            </div>
                            <div class="col-md-3">
                                <strong><i class="fas fa-calendar-alt me-1"></i>Original Date:</strong><br>
                                {{ $sourceEntry->date->format('d M Y') }}
                            </div>
                            <div class="col-md-3">
                                <strong><i class="fas fa-user me-1"></i>Paid To:</strong><br>
                                {{ $sourceEntry->paid_to }}
                            </div>
                            <div class="col-md-3">
                                <strong><i class="fas fa-money-bill-wave me-1"></i>Amount:</strong><br>
                                RM {{ number_format($sourceEntry->dr_total, 2) }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control" 
                                       value="{{ old('date', date('Y-m-d')) }}" required>
                                <small class="text-muted">Original date: {{ $sourceEntry->date->format('d M Y') }}</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Entry Code <span class="text-danger">*</span></label>
                                <input type="text" name="entry_code" class="form-control bg-light" readonly
                                       value="{{ old('entry_code', $entryCode) }}" required>
                                <small class="text-success">New entry code will be generated</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Fund <span class="text-danger">*</span></label>
                                <select name="fund_id" class="form-control" required>
                                    @foreach($funds as $fund)
                                        <option value="{{ $fund->id }}" {{ old('fund_id', $sourceEntry->fund_id) == $fund->id ? 'selected' : '' }}>
                                            {{ $fund->name }}{{ $fund->code ? ' (' . $fund->code . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                                <select name="payment_mode" class="form-control" id="paymentMode" required>
                                    <option value="CASH" {{ old('payment_mode', $sourceEntry->payment) == 'CASH' ? 'selected' : '' }}>Cash</option>
                                    <option value="CHEQUE" {{ old('payment_mode', $sourceEntry->payment) == 'CHEQUE' ? 'selected' : '' }}>Cheque</option>
                                    <option value="ONLINE" {{ old('payment_mode', $sourceEntry->payment) == 'ONLINE' ? 'selected' : '' }}>Online Transfer</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Bank/Cash Account <span class="text-danger">*</span></label>
                                <select name="credit_account" class="form-control" required>
                                    <option value="">Select Account</option>
                                    @foreach($bankLedgers as $ledger)
                                        <option value="{{ $ledger->id }}" {{ old('credit_account', $creditAccount->ledger_id) == $ledger->id ? 'selected' : '' }}>
                                            {{ $ledger->left_code }}/{{ $ledger->right_code }} - {{ $ledger->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Paid To <span class="text-danger">*</span></label>
                                <input type="text" name="paid_to" class="form-control" 
                                       value="{{ old('paid_to', $sourceEntry->paid_to) }}" required placeholder="Enter payee name">
                            </div>
                        </div>
                    </div>

                    <!-- Discount Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="discountCheck" {{ $discountItem ? 'checked' : '' }}>
                                <label class="form-check-label" for="discountCheck">
                                   </i>Apply Discount
                                    @if($discountItem)
                                        <span class="badge bg-warning text-dark ms-2">Originally: RM {{ number_format($discountItem->amount, 2) }}</span>
                                    @endif
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row" id="discountSection" style="{{ $discountItem ? '' : 'display: none;' }}">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Discount Account <span class="text-danger">*</span></label>
                                <select name="discount_ledger" class="form-control">
                                    <option value="">Select Discount Account</option>
                                    @foreach($debitLedgers as $ledger)
                                        @if(substr($ledger->left_code, 0, 1) == '4' || substr($ledger->left_code, 0, 1) == '8')
                                            <option value="{{ $ledger->id }}" {{ $discountItem && $discountItem->ledger_id == $ledger->id ? 'selected' : '' }}>
                                                {{ $ledger->left_code }}/{{ $ledger->right_code }} - {{ $ledger->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Discount Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" name="discount_amount" class="form-control" id="discountAmount"
                                           step="0.01" min="0" value="{{ $discountItem ? $discountItem->amount : 0.00 }}" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Method Details -->
            <div class="card mb-3" id="chequeDetails" style="{{ $sourceEntry->payment == 'CHEQUE' ? '' : 'display: none;' }}">
                <div class="card-header bg-warning">
                    <h6 class="mb-0 text-white"><i class="fas fa-money-check me-2"></i>Cheque Details</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Cheque Number</label>
                                <input type="text" name="cheque_no" class="form-control" 
                                       value="{{ old('cheque_no', $sourceEntry->cheque_no) }}" placeholder="Enter new cheque number">
                                @if($sourceEntry->cheque_no)
                                    <small class="text-muted">Original: {{ $sourceEntry->cheque_no }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Cheque Date</label>
                                <input type="date" name="cheque_date" class="form-control" 
                                       value="{{ old('cheque_date', isset($sourceEntry->cheque_date) ? $sourceEntry->cheque_date->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-3" id="onlineDetails" style="{{ $sourceEntry->payment == 'ONLINE' ? '' : 'display: none;' }}">
                <div class="card-header bg-info">
                    <h6 class="mb-0 text-white"><i class="fas fa-globe me-2"></i>Online Transfer Details</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Transaction Number</label>
                                <input type="text" name="transaction_no" class="form-control" 
                                       value="{{ old('transaction_no', $sourceEntry->cheque_no) }}" placeholder="Enter new transaction number">
                                @if($sourceEntry->payment == 'ONLINE' && $sourceEntry->cheque_no)
                                    <small class="text-muted">Original: {{ $sourceEntry->cheque_no }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Transaction Date</label>
                                <input type="date" name="transaction_date" class="form-control" 
                                       value="{{ old('transaction_date', isset($sourceEntry->cheque_date) ? $sourceEntry->cheque_date->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Items -->
            <div class="card mb-3">
                <div class="card-header bg-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-white"><i class="fas fa-list me-2"></i>Payment Items (Copied)</h6>
                        <small class="text-white-50">Original had {{ count($debitItems) }} item(s)</small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> All payment items have been copied from the original voucher. 
                        You can modify amounts and details as needed.
                    </div>
                    
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
                                @php $itemIndex = 0; @endphp
                                @foreach($debitItems as $item)
                                <tr class="payment-item">
                                    <td>
                                        <select name="items[{{ $itemIndex }}][ledger_id]" class="form-control item-ledger" required>
                                            <option value="">Select Account</option>
                                            @foreach($debitLedgers as $ledger)
                                                <option value="{{ $ledger->id }}" {{ $item->ledger_id == $ledger->id ? 'selected' : '' }}>
                                                    {{ $ledger->left_code }}/{{ $ledger->right_code }} - {{ $ledger->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="number" name="items[{{ $itemIndex }}][amount]" class="form-control item-amount" 
                                                   step="0.01" min="0.01" value="{{ $item->amount }}" required placeholder="0.00">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $itemIndex }}][details]" class="form-control" 
                                               placeholder="Optional details" value="{{ $item->details }}">
                                    </td>
                                    <td class="text-center">
                                        @if($loop->first)
                                        <button type="button" class="btn btn-sm btn-success add-item" title="Add Row">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        @else
                                        <button type="button" class="btn btn-sm btn-danger remove-item" title="Remove Row">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @php $itemIndex++; @endphp
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <th class="text-end">Total Amount:</th>
                                    <th><span id="itemsTotal" class="fw-bold">RM {{ number_format($sourceEntry->dr_total, 2) }}</span></th>
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
                    <h6 class="mb-0"><i class="fas fa-comment me-2"></i>Payment Description</h6>
                </div>
                <div class="card-body">
                    <textarea name="narration" class="form-control" rows="3" 
                              placeholder="Enter payment description or purpose">{{ old('narration', $sourceEntry->narration) }}</textarea>
                    @if($sourceEntry->narration)
                        <small class="text-muted mt-2">
                            <strong>Original description:</strong> {{ $sourceEntry->narration }}
                        </small>
                    @endif
                </div>
            </div>
            
            <!-- Amount in Words -->
            <div class="card mb-4">
                <div class="card-header bg-secondary">
                    <h6 class="mb-0 text-white"><i class="fas fa-spell-check me-2"></i>Amount in Words</h6>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded">
                        @php
                        if(!function_exists('numberToWords')){
                            function numberToWords($amount) {
                                if ($amount == 0) return 'ZERO';
                                
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
                                $senWords = $sen > 0 ? ' AND ' . $toWords($sen) . ' SEN' : '';
                                
                                return "RINGGIT {$ringgitWords}{$senWords}";
                            }
                        }
                        $dr_total = $sourceEntry->dr_total;
                        if($discountItem && !empty($discountItem->amount)) $dr_total -= $discountItem->amount;
                        @endphp
                        <h5 id="amountInWords" class="mb-0 text-primary">
                            RM {{ number_format($dr_total, 2) }}<br>
                            <small>{{ strtoupper(numberToWords($dr_total)) }} ONLY</small>
                        </h5>
                    </div>
                </div>
            </div>
            
            <!-- Submit Buttons -->
            
                    <div class="d-flex justify-content-between">
                      
                        <div>
                      
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save me-1"></i>Save New Payment
                            </button>
                              <a href="{{ route('accounts.payment.list') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                        </div>
                    </div>
             
            
            <!-- Hidden template for ledger options -->
            <select id="ledgerTemplate" class="d-none">
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
    let itemIndex = {{ count($debitItems) }};
    
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
        const ledgerOptions = $('#ledgerTemplate').html();
        const newRow = `
            <tr class="payment-item">
                <td>
                    <select name="items[${itemIndex}][ledger_id]" class="form-control item-ledger" required>
                        <option value="">Select Account</option>
                        ${ledgerOptions}
                    </select>
                </td>
                <td>
                    <div class="input-group">
                        <input type="number" name="items[${itemIndex}][amount]" class="form-control item-amount" 
                               step="0.01" min="0.01" required placeholder="0.00">
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
        
        itemIndex++;
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
    
    // Initialize calculation
    calculateTotal();
    
    // Initialize tooltips
    $('[title]').tooltip();

    // Show helpful notification on page load
    setTimeout(function() {
        if (!$('.alert-success').length) {
            $('body').prepend(`
                <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; max-width: 400px;">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Payment Copied!</strong><br>
                    All data has been copied from <code>${{ $sourceEntry->entry_code }}</code>. 
                    Modify as needed and save to create a new payment voucher.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
        }
    }, 500);
});
</script>
@endpush
@endsection