@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-light rounded px-3 py-2">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}" class="text-decoration-none">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="#" class="text-decoration-none">Reports</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">General Ledger</li>
            </ol>
        </nav>

        <!-- Main Panel -->
        <div class="card shadow-sm">
            <!-- Header -->
            <div class="card-header bg-primary text-white">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">
                            <i class="fas fa-book me-2"></i>General Ledger Report
                        </h5>
                    </div>
                    <div class="col-md-6 text-end">
                        <small class="opacity-75">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Financial Year: {{ date('d M Y', strtotime($activeYear->from_year_month)) }} to {{ date('d M Y', strtotime($activeYear->to_year_month)) }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="card-body">
                <form method="GET" action="{{ route('accounts.reports.general-ledger') }}" id="glReportForm">
                    <div class="row g-3">
                        <!-- Ledger Selection -->
                        <div class="col-md-4">
                            <label class="form-label">
                                Select Ledger(s)
                                <span class="text-danger">*</span>
                            </label>
                            <div class="ledger-select-container">
                                <div class="custom-multiselect">
                                    <div class="multiselect-dropdown">
                                        <div class="multiselect-input" id="multiselectInput">
                                            <span>Search and select ledgers...</span>
                                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                                        </div>
                                        <div class="multiselect-dropdown-content" id="dropdownContent">
                                            <div class="search-box">
                                                <input type="text" id="ledgerSearch" placeholder="Search ledgers..." class="form-control form-control-sm">
                                            </div>
                                            <div class="select-all-container">
                                                <button type="button" class="btn btn-sm btn-outline-primary me-2" id="selectAllBtn">Select All</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">Clear All</button>
                                            </div>
                                            <div class="options-container" id="optionsContainer">
                                                @foreach($ledgers as $ledger)
                                                <label class="multiselect-option">
                                                    <input type="checkbox" name="ledger_ids[]" value="{{ $ledger->id }}"
                                                        {{ in_array($ledger->id, $selectedLedgerIds) ? 'checked' : '' }}>
                                                    <span class="checkmark"></span>
                                                    <span class="option-text">{{ $ledger->left_code }}/{{ $ledger->right_code }} - {{ $ledger->name }}</span>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Type -->
                        <div class="col-md-2">
                            <label class="form-label">
                                Invoice Type
                            </label>
                            <select name="invoice_type" class="form-select">
                                <option value="all" {{ request('invoice_type') == 'all' ? 'selected' : '' }}>All Types</option>
                                <option value="1" {{ request('invoice_type') == '1' ? 'selected' : '' }}>Sales</option>
                                <option value="2" {{ request('invoice_type') == '2' ? 'selected' : '' }}>Purchases</option>
                                <option value="manual" {{ request('invoice_type') == 'manual' ? 'selected' : '' }}>Manual Entries</option>
                            </select>
                        </div>

                        <!-- From Date -->
                        <div class="col-md-2">
                            <label class="form-label">
                                From Date
                                <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="from_date" class="form-control"
                                value="{{ $fromDate }}"
                                min="{{ $activeYear->from_year_month }}"
                                required>
                        </div>

                        <!-- To Date -->
                        <div class="col-md-2">
                            <label class="form-label">
                                To Date
                                <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="to_date" class="form-control"
                                value="{{ $toDate }}"
                                required>
                        </div>

                        <!-- Generate Button -->
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100" id="generateBtn">
                                <i class="fas fa-search me-1"></i>Generate
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            @if(!empty($ledgerReports))
            <!-- Export Options -->
            <div class="card-body border-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="mb-0 text-muted">


                        </h6>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end">
                            <div class="btn-group" role="group" aria-label="Report Actions">
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="printReport()">
                                    <i class="fas fa-print me-1"></i> Print
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="exportReport('pdf')">
                                    <i class="fas fa-file-pdf me-1"></i> PDF
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="exportReport('excel')">
                                    <i class="fas fa-file-excel me-1"></i> Excel
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Report Content -->
            <div class="card-body">
                @foreach($ledgerReports as $index => $report)
                <div class="ledger-report-section {{ $index > 0 ? 'mt-5' : '' }}">
                    <!-- Ledger Header -->
                    <div class="alert alert-secondary mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-1">
                                  {{ $report['ledger']->name }}
                                </h5>
                                <small class="opacity-75">
                                    Account Code: {{ $report['ledger']->left_code }}/{{ $report['ledger']->right_code }}
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <small class="opacity-75">
                                    Period: {{ date('d M Y', strtotime($fromDate)) }} to {{ date('d M Y', strtotime($toDate)) }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th width="10%">Date</th>
                                    <th width="15%">Voucher No</th>
                                    <th width="15%">Type</th>
                                    <th width="25%">Particulars</th>
                                    <th width="12%" class="text-end">Debit (₹)</th>
                                    <th width="12%" class="text-end">Credit (₹)</th>
                                    <th width="11%" class="text-end">Balance (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Opening Balance -->
                                <tr class="table-success">
                                    <td colspan="4" class="fw-bold">
                                        Opening Balance
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format($report['openingBalance']['debit'], 2) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($report['openingBalance']['credit'], 2) }}</td>
                                    <td class="text-end fw-bold">
                                        @php
                                        $openingNet = $report['openingBalance']['debit'] - $report['openingBalance']['credit'];
                                        @endphp
                                        <span class="{{ $openingNet >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $openingNet >= 0 ? number_format(abs($openingNet), 2) : '(' . number_format(abs($openingNet), 2) . ')' }}
                                        </span>
                                    </td>
                                </tr>

                                <!-- Transactions -->
                                @foreach($report['transactions'] as $transaction)
                                <tr class="transaction-row">
                                    <td>
                                        <small class="text-muted">{{ date('d M Y', strtotime($transaction->entry->date)) }}</small>
                                    </td>
                                    <td>
                                        @php
                                        $entryId = $transaction->entry->id;
                                        $invType = $transaction->entry->inv_type;
                                        $entryTypeId = $transaction->entry->entrytype_id;

                                        // Determine the appropriate route based on entry type and inv_type
                                        if ($entryTypeId == 1) {
                                        if ($invType === null || $invType === '') {
                                        $url = route('accounts.receipt.edit', $entryId);
                                        } else {
                                        $url = route('accounts.receipt.view', $entryId);
                                        }
                                        } elseif ($entryTypeId == 2) {
                                        if ($invType === null || $invType === '') {
                                        $url = route('accounts.payment.edit', $entryId);
                                        } else {
                                        $url = route('accounts.payment.view', $entryId);
                                        }
                                        } elseif ($entryTypeId == 4) {
                                        if ($invType === null || $invType === '') {
                                        $url = route('accounts.journal.edit', $entryId);
                                        } else {
                                        $url = route('accounts.journal.view', $entryId);
                                        }
                                        } else {
                                        $url = route('chart_of_accounts.ledger.view', $entryId);
                                        }
                                        @endphp

                                        <a href="{{ $url }}" class="voucher-link text-decoration-none" target="_blank">
                                            <i class="fas fa-external-link-alt me-1"></i>{{ $transaction->entry->entry_code }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $transaction->entry->entry_type_name }}</span>
                                        @if($transaction->entry->inv_type)
                                        <br><small class="badge bg-secondary mt-1">
                                            {{ $transaction->entry->inv_type == 1 ? 'Sales' : 'Purchase' }}
                                        </small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                        $oppositeEntries = $transaction->entry->entryItems
                                        ->where('id', '!=', $transaction->id)
                                        ->where('dc', '!=', $transaction->dc);
                                        $particulars = [];
                                        foreach($oppositeEntries as $opposite) {
                                        $particulars[] = @$opposite->ledger->name;
                                        }
                                        @endphp
                                        <span class="fw-semibold">{{ implode(', ', $particulars) }}</span>
                                        @if($transaction->entry->narration)
                                        <br><small class="text-muted fst-italic">{{ $transaction->entry->narration }}</small>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($transaction->dc == 'D')
                                        <span class="text-success fw-semibold">{{ number_format($transaction->amount, 2) }}</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($transaction->dc == 'C')
                                        <span class="text-danger fw-semibold">{{ number_format($transaction->amount, 2) }}</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="{{ $transaction->balance_type == 'Dr' ? 'text-success' : 'text-danger' }} fw-semibold">
                                            {{ $transaction->balance_type == 'Dr' ? number_format($transaction->running_balance, 2) : '(' . number_format($transaction->running_balance, 2) . ')' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach

                                <!-- Closing Balance -->
                                <tr class="table-warning">
                                    <td colspan="4" class="fw-bold">
                                        Closing Balance
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format($report['closingBalance']['debit'], 2) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($report['closingBalance']['credit'], 2) }}</td>
                                    <td class="text-end fw-bold">
                                        @php
                                        $closingNet = $report['closingBalance']['debit'] - $report['closingBalance']['credit'];
                                        @endphp
                                        <span class="{{ $closingNet >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $closingNet >= 0 ? number_format(abs($closingNet), 2) : '(' . number_format(abs($closingNet), 2) . ')' }}
                                        </span>
                                    </td>
                                </tr>

                                <!-- Net Activity -->
                                @php
                                $periodDebits = 0;
                                $periodCredits = 0;
                                foreach($report['transactions'] as $transaction) {
                                if($transaction->dc == 'D') {
                                $periodDebits += $transaction->amount;
                                } else {
                                $periodCredits += $transaction->amount;
                                }
                                }
                                $netActivity = $periodDebits - $periodCredits;
                                @endphp
                                <tr class="table-info">
                                    <td colspan="4" class="fw-bold">
                                        Net Activity
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format($periodDebits, 2) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($periodCredits, 2) }}</td>
                                    <td class="text-end fw-bold">
                                        <span class="{{ $netActivity >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $netActivity >= 0 ? number_format(abs($netActivity), 2) : '(' . number_format(abs($netActivity), 2) . ')' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            </div>

            @endif
        </div>
    </div>
</div>

<style>
    .table thead th {
        color: black;
        background-color: #ffffff;
        border-bottom: 2px #010102ff;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }

    /* Custom Multiselect Dropdown */
    .custom-multiselect {
        position: relative;
        width: 100%;
    }

    .multiselect-dropdown {
        position: relative;
    }

    .multiselect-input {
        min-height: 38px;
        padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        background-color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .multiselect-input:hover {
        border-color: #86b7fe;
    }

    .multiselect-input.active {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .placeholder {
        color: #6c757d;
        flex-grow: 1;
    }

    .dropdown-arrow {
        transition: transform 0.2s;
    }

    .dropdown-arrow.rotated {
        transform: rotate(180deg);
    }

    .multiselect-dropdown-content {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ced4da;
        border-top: none;
        border-radius: 0 0 0.375rem 0.375rem;
        z-index: 1000;
        max-height: 300px;
        overflow-y: auto;
        display: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .search-box {
        padding: 10px;
        border-bottom: 1px solid #e9ecef;
    }

    .select-all-container {
        padding: 10px;
        border-bottom: 1px solid #e9ecef;
        background-color: #f8f9fa;
    }

    .options-container {
        max-height: 200px;
        overflow-y: auto;
    }

    .multiselect-option {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        cursor: pointer;
        transition: background-color 0.15s;
        margin: 0;
    }

    .multiselect-option:hover {
        background-color: #f8f9fa;
    }

    .multiselect-option input[type="checkbox"] {
        margin-right: 8px;
        cursor: pointer;
    }

    .option-text {
        flex-grow: 1;
        font-size: 0.875rem;
    }

    /* Selected items display */
    .selected-items {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 4px;
    }

    .selected-item {
        background-color: #e7f3ff;
        color: #0969da;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .remove-item {
        cursor: pointer;
        font-weight: bold;
    }

    /* Table Enhancements */
    .transaction-row:hover {
        background-color: #f8f9fa !important;
    }

    .voucher-link {
        color: #0d6efd;
        font-weight: 500;
    }

    .voucher-link:hover {
        color: #0a58ca;
    }

    /* Loading Animation */
    .btn.loading {
        pointer-events: none;
        opacity: 0.6;
    }

    .btn.loading::after {
        content: "";
        display: inline-block;
        width: 16px;
        height: 16px;
        margin-left: 8px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Print Styles */
    @media print {

        .card-header,
        .btn-group,
        .filter-form {
            display: none !important;
        }

        .ledger-report-section {
            page-break-after: always;
        }

        .ledger-report-section:last-child {
            page-break-after: auto;
        }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .btn-group {
            flex-direction: column;
            width: 100%;
        }

        .btn-group .btn {
            margin-bottom: 5px;
        }

        .table-responsive {
            font-size: 0.875rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const multiselectInput = document.getElementById('multiselectInput');
        const dropdownContent = document.getElementById('dropdownContent');
        const dropdownArrow = multiselectInput.querySelector('.dropdown-arrow');
        const searchInput = document.getElementById('ledgerSearch');
        const optionsContainer = document.getElementById('optionsContainer');
        const selectAllBtn = document.getElementById('selectAllBtn');
        const deselectAllBtn = document.getElementById('deselectAllBtn');
        const placeholder = multiselectInput.querySelector('.placeholder');

        // Toggle dropdown
        multiselectInput.addEventListener('click', function() {
            const isOpen = dropdownContent.style.display === 'block';
            dropdownContent.style.display = isOpen ? 'none' : 'block';
            dropdownArrow.classList.toggle('rotated', !isOpen);
            multiselectInput.classList.toggle('active', !isOpen);

            if (!isOpen) {
                searchInput.focus();
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.custom-multiselect')) {
                dropdownContent.style.display = 'none';
                dropdownArrow.classList.remove('rotated');
                multiselectInput.classList.remove('active');
            }
        });

        // Search functionality
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const options = optionsContainer.querySelectorAll('.multiselect-option');

            options.forEach(option => {
                const text = option.querySelector('.option-text').textContent.toLowerCase();
                option.style.display = text.includes(searchTerm) ? 'flex' : 'none';
            });
        });

        // Select All functionality
        selectAllBtn.addEventListener('click', function() {
            const visibleCheckboxes = optionsContainer.querySelectorAll('.multiselect-option:not([style*="display: none"]) input[type="checkbox"]');
            visibleCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            updateSelectedDisplay();
        });

        // Deselect All functionality
        deselectAllBtn.addEventListener('click', function() {
            const checkboxes = optionsContainer.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            updateSelectedDisplay();
        });

        // Update selected items display
        function updateSelectedDisplay() {
            const checkedBoxes = optionsContainer.querySelectorAll('input[type="checkbox"]:checked');

            if (checkedBoxes.length === 0) {
                placeholder.textContent = 'Search and select ledgers...';
                placeholder.style.color = '#6c757d';
            } else {
                placeholder.textContent = `${checkedBoxes.length} ledger(s) selected`;
                placeholder.style.color = '#212529';
            }
        }

        // Initialize display
        updateSelectedDisplay();

        // Listen for checkbox changes
        optionsContainer.addEventListener('change', function(e) {
            if (e.target.type === 'checkbox') {
                updateSelectedDisplay();
            }
        });

        // Form submission with loading state
        const generateBtn = document.getElementById('generateBtn');
        const form = document.getElementById('glReportForm');

        form.addEventListener('submit', function() {
            generateBtn.classList.add('loading');
            generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating...';
        });
    });

    // Export functions
    function exportReport(type) {
        const form = document.getElementById('glReportForm');
        const formData = new FormData(form);

        let url = '{{ route("accounts.reports.general-ledger") }}?';

        // Handle multiple ledger IDs
        const selectedLedgers = Array.from(document.querySelectorAll('input[name="ledger_ids[]"]:checked')).map(cb => cb.value);
        if (selectedLedgers && selectedLedgers.length > 0) {
            selectedLedgers.forEach(function(ledgerId) {
                url += 'ledger_ids[]=' + encodeURIComponent(ledgerId) + '&';
            });
        }

        // Add other form fields
        for (let [key, value] of formData.entries()) {
            if (key !== 'ledger_ids[]') {
                url += key + '=' + encodeURIComponent(value) + '&';
            }
        }

        url += 'export=' + type;

        window.location.href = url;
    }

    function printReport() {
        const form = document.getElementById('glReportForm');
        const formData = new FormData(form);

        let url = '{{ route("accounts.reports.general-ledger") }}?';

        // Handle multiple ledger IDs
        const selectedLedgers = Array.from(document.querySelectorAll('input[name="ledger_ids[]"]:checked')).map(cb => cb.value);
        if (selectedLedgers && selectedLedgers.length > 0) {
            selectedLedgers.forEach(function(ledgerId) {
                url += 'ledger_ids[]=' + encodeURIComponent(ledgerId) + '&';
            });
        }

        // Add other form fields
        for (let [key, value] of formData.entries()) {
            if (key !== 'ledger_ids[]') {
                url += key + '=' + encodeURIComponent(value) + '&';
            }
        }

        url += 'export=print';

        window.open(url, '_blank');
    }

    // Form validation
    document.getElementById('glReportForm').addEventListener('submit', function(e) {
        const selectedLedgers = document.querySelectorAll('input[name="ledger_ids[]"]:checked');
        if (selectedLedgers.length === 0) {
            e.preventDefault();
            alert('Please select at least one ledger to generate the report.');
            return false;
        }
    });
</script>
@endsection