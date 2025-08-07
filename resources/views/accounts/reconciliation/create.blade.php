@extends('layouts.app')
@section('title', 'Start New Reconciliation')
@section('content')

<div class="row">
    <div class="col-12">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('accounts.reconciliation.index') }}">Bank Reconciliation</a></li>
                <li class="breadcrumb-item active">Start New Reconciliation</li>
            </ol>
        </nav>

        <form method="POST" action="{{ route('accounts.reconciliation.start') }}" id="reconciliationForm" onsubmit="return validateAndSubmit()">
            @csrf

            <!-- Header Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-plus-circle text-primary me-2"></i>
                        <h5 class="mb-0">Start New Bank Reconciliation</h5>
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

                    <!-- Form Fields -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Select Bank Account <span class="text-danger">*</span></label>
                                <select name="ledger_id" class="form-control" required>
                                    <option value="">-- Select Bank Account --</option>
                                    @foreach($bankLedgers as $ledger)
                                    <option value="{{ $ledger->id }}" {{ old('ledger_id') == $ledger->id ? 'selected' : '' }}>
                                        {{ $ledger->name }}
                                    </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Only bank accounts enabled for reconciliation are shown</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Reconciliation Month <span class="text-danger">*</span></label>
                                <input type="month" name="month" class="form-control"
                                    value="{{ old('month', date('Y-m', strtotime('-1 month'))) }}"
                                    max="{{ date('Y-m') }}" required>
                                <small class="text-muted">Select the month you want to reconcile</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Bank Statement Closing Balance <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" name="statement_closing_balance" class="form-control"
                                        step="0.01" value="{{ old('statement_closing_balance') }}"
                                        placeholder="0.00" required>
                                </div>
                                <small class="text-muted">Enter the closing balance as shown in your bank statement</small>
                            </div>
                        </div>

                        @if($currentYear)
                        @php
                        $fromDate = \Carbon\Carbon::parse($currentYear->from_year_month)->format('d M Y');
                        $toDate = \Carbon\Carbon::parse($currentYear->to_year_month)->format('d M Y');
                        @endphp
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Current Accounting Year</label>
                                <input type="text" class="form-control bg-light" readonly
                                    value="{{ $fromDate }} to {{ $toDate }}">
                                <small class="text-muted">Your current accounting period</small>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
            <!-- Submit Buttons -->

            <div class="d-flex gap-3  mb-4">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-arrow-right me-1"></i>Start Reconciliation
                </button>
                <a href="{{ route('accounts.reconciliation.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i>Cancel
                </a>

            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Form validation and submission
        window.validateAndSubmit = function() {
            const form = document.getElementById('reconciliationForm');
            const submitBtn = document.getElementById('submitBtn');

            if (form.checkValidity()) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Starting...';
                return true;
            }
            return false;
        };

        // Auto-format balance input
        $('input[name="statement_closing_balance"]').on('blur', function() {
            if (this.value && !isNaN(this.value)) {
                this.value = parseFloat(this.value).toFixed(2);
            }
        });

        // Month validation
        $('input[name="month"]').on('change', function() {
            const selectedMonth = new Date(this.value + '-01');
            const currentDate = new Date();
            const maxMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);

            if (selectedMonth > maxMonth) {
                showAlert('Cannot reconcile future months', 'warning');
                this.value = '';
            }
        });

        // Bank account change handler
        $('select[name="ledger_id"]').on('change', function() {
            if (this.value) {
                // You could add AJAX call here to get bank-specific information
                console.log('Bank account selected:', this.value);
            }
        });

        function showAlert(message, type = 'info') {
            const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
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

        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endpush
@endsection