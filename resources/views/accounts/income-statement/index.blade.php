@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb pl-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"></i> Home</a></li>
                <li class="breadcrumb-item">Accounts</li>
                <li class="breadcrumb-item active" aria-current="page">Income Statement</li>
            </ol>
        </nav>

        <br>
        <div class="ms-panel">
            <div class="card shadow-sm">
                <!-- Header -->
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>Income Statement
                            </h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="opacity-75">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Financial Year: {{ date('d-m-Y', strtotime($activeYear->from_year_month)) }} to
                                {{ date('d-m-Y', strtotime($activeYear->to_year_month)) }}
                            </small>
                        </div>
                    </div>
                </div>


                <div class="card-body">
                    <form method="GET" action="{{ route('accounts.income-statement') }}" id="incomeStatementForm">
                        <div class="d-flex flex-column align-items-end mb-3" style="margin-top: -24px;">
                            <label>&nbsp;</label>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-info" onclick="printReport()">
                                    <i class="fas fa-print"></i> Print
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="exportReport('pdf')">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </button>
                                <button type="button" class="btn btn-sm btn-success" onclick="exportReport('excel')">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label>Display Type</label>
                                <select name="display_type" class="form-control" onchange="validateDateRange()">
                                    <option value="full" {{ $displayType == 'full' ? 'selected' : '' }}>Full</option>
                                    <option value="monthly" {{ $displayType == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label>From</label>
                                <input type="date" name="from_date" class="form-control"
                                    value="{{ $fromDate }}"
                                    min="{{ $activeYear->from_year_month }}"
                                    max="{{ $activeYear->to_year_month }}"
                                    onchange="validateDateRange()"
                                    required>
                            </div>

                            <div class="col-md-2">
                                <label>To</label>
                                <input type="date" name="to_date" class="form-control"
                                    value="{{ $toDate }}"
                                    onchange="validateDateRange()"
                                    required>
                            </div>

                            <div class="col-md-3">
                                <label>Fund</label>
                                <select name="fund_id" class="form-control">
                                    <option value="all">All Funds</option>
                                    @foreach($funds as $fund)
                                    <option value="{{ $fund->id }}" {{ $selectedFundId == $fund->id ? 'selected' : '' }}>
                                        {{ $fund->code }} - {{ $fund->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 my-4">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-success btn-block">
                                    Submit
                                </button>
                            </div>
                        </div>
                    </form>

                    @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif

                    @if(isset($incomeStatementData))
                    @if($displayType == 'full')
                    @include('accounts.income-statement.partials.full_view', [
                    'incomeStatementData' => $incomeStatementData,
                    'fromDate' => $fromDate,
                    'toDate' => $toDate,
                    'selectedFundId' => $selectedFundId
                    ])
                    @else
                    @include('accounts.income-statement.partials.monthly_view', [
                    'incomeStatementData' => $incomeStatementData,
                    'fromDate' => $fromDate,
                    'toDate' => $toDate,
                    'selectedFundId' => $selectedFundId
                    ])
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Ledger link styles */
        .ledger-link {
            color: #007bff;
            text-decoration: none;
            cursor: pointer;
        }

        .ledger-link:hover {
            color: #0056b3;
            text-decoration: underline;
        }
    </style>

    <script>
        function validateDateRange() {
            const displayType = document.querySelector('[name="display_type"]').value;
            const fromDate = document.querySelector('[name="from_date"]').value;
            const toDate = document.querySelector('[name="to_date"]').value;

            if (displayType === 'monthly' && fromDate && toDate) {
                const from = new Date(fromDate);
                const to = new Date(toDate);
                const monthsDiff = (to.getFullYear() - from.getFullYear()) * 12 + (to.getMonth() - from.getMonth());

                if (monthsDiff > 11) {
                    alert('Monthly view cannot exceed 12 months. Please adjust your date range.');
                }
            }
        }

        function exportReport(type) {
            const form = document.getElementById('incomeStatementForm');
            const formData = new FormData(form);

            let url = '{{ route("accounts.income-statement") }}?';
            for (let [key, value] of formData.entries()) {
                url += key + '=' + encodeURIComponent(value) + '&';
            }
            url += 'export=' + type;

            window.location.href = url;
        }

        function printReport() {
            const form = document.getElementById('incomeStatementForm');
            const formData = new FormData(form);

            let url = '{{ route("accounts.income-statement") }}?';
            for (let [key, value] of formData.entries()) {
                url += key + '=' + encodeURIComponent(value) + '&';
            }
            url += 'export=print';

            window.open(url, '_blank');
        }

        /**
         * Open ledger details in general ledger from income statement
         * Same concept as balance sheet and trial balance
         */
        function openIncomeStatementLedgerReport(ledgerId, ledgerName) {
            const fromDate = document.querySelector('input[name="from_date"]').value || '{{ $fromDate }}';
            const toDate = document.querySelector('input[name="to_date"]').value || '{{ $toDate }}';
            const fundId = document.querySelector('select[name="fund_id"]').value || 'all';

            // Build the general ledger URL with date filters, ledger ID, fund, and invoice type
            let url = '{{ route("accounts.reports.general-ledger") }}';
            url += '?ledger_ids[]=' + encodeURIComponent(ledgerId);
            url += '&from_date=' + encodeURIComponent(fromDate);
            url += '&to_date=' + encodeURIComponent(toDate);
            url += '&invoice_type=all';

            // Add fund filter if specific fund is selected
            if (fundId !== 'all') {
                url += '&fund_id=' + encodeURIComponent(fundId);
            }

            // Open in new tab
            window.open(url, '_blank');
        }
    </script>
    @endsection