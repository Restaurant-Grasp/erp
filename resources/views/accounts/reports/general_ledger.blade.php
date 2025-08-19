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
                           <div class="enhanced-multiselect">
    <div class="multiselect-container">
        <div class="multiselect-input" id="multiselectInput">
            <div class="input-content">
                <div class="selected-items" id="selectedItems"></div>
                <span class="placeholder-text" id="placeholderText">Search and select ledgers...</span>
            </div>
            <i class="fas fa-chevron-down dropdown-arrow" id="dropdownArrow"></i>
        </div>
        <div class="multiselect-dropdown-content" id="dropdownContent">
            <!-- Search section -->
            <div class="search-section">
                <input type="text" id="ledgerSearch" placeholder="Type to search ledgers..." class="search-input">
            </div>
            <!-- Action buttons -->
            <div class="actions-section">
                <button type="button" class="action-btn" id="selectAllBtn">Select All</button>
                <button type="button" class="action-btn" id="deselectAllBtn">Clear All</button>
                <button type="button" class="action-btn" id="selectVisibleBtn">Select Visible</button>
            </div>
            <!-- Options list -->
            <div class="options-section" id="optionsContainer">
                @foreach($ledgers as $ledger)
                <label class="multiselect-option">
                    <input type="checkbox" name="ledger_ids[]" value="{{ $ledger->id }}" class="option-checkbox">
                    <span class="option-text">
                        <span class="option-code">{{ $ledger->left_code }}/{{ $ledger->right_code }}</span>
                        <span class="option-name">{{ $ledger->name }}</span>
                    </span>
                </label>
                @endforeach
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
    /* Enhanced Multi-Select Dropdown Styles */
        .enhanced-multiselect {
            position: relative;
            width: 100%;
        }

        .multiselect-container {
            position: relative;
        }

        .multiselect-input {
            min-height: 42px;
            padding: 8px 40px 8px 12px;
            border: 2px solid #ced4da;
            border-radius: 8px;
            background-color: white;
            cursor: pointer;
            display: block;
            width: 100%;
            transition: all 0.2s ease;
            position: relative;
        }

        .multiselect-input:hover {
            border-color: #0d6efd;
        }

        .multiselect-input.active {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        .multiselect-input.has-selections {
            padding: 4px 40px 4px 8px;
        }



        .placeholder-text {
            color: black;
            font-size: 14px;
            pointer-events: none;
        }

        .dropdown-arrow {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.2s ease;
            color: black;
            pointer-events: none;
        }

        .dropdown-arrow.rotated {
            transform: translateY(-50%) rotate(180deg);
        }

        /* Selected Items */
        .selected-items {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            flex: 1;
        }

        .selected-item {
            background: linear-gradient(135deg, #e7f3ff, #d4edff);
            color: #0969da;
            padding: 4px 8px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
            border: 1px solid #b6d7ff;
            max-width: 200px;
        }

        .selected-item-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .remove-item {
            cursor: pointer;
            font-weight: bold;
            color: #dc3545;
            margin-left: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            transition: background-color 0.2s;
        }

        .remove-item:hover {
            background-color: rgba(220, 53, 69, 0.1);
        }

        /* Dropdown Content */
        .multiselect-dropdown-content {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #0d6efd;
            border-top: none;
            border-radius: 0 0 8px 8px;
            z-index: 1050;
            max-height: 320px;
            overflow: hidden;
            display: none;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .search-section {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            background: #f8f9fa;
        }

        .search-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.1);
        }

        .actions-section {
            padding: 8px 12px;
            border-bottom: 1px solid #e9ecef;
            background: #f8f9fa;
            display: flex;
            gap: 8px;
        }

        .action-btn {
            background: none;
            border: 1px solid #ced4da;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
.table thead th {
    color: black;
}
        .action-btn:hover {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        .options-section {
            max-height: 200px;
            overflow-y: auto;
        }

        .multiselect-option {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            cursor: pointer;
            transition: background-color 0.15s;
            margin: 0;
            border-bottom: 1px solid #f1f3f4;
        }

        .multiselect-option:hover {
            background-color: #f8f9fa;
        }

        .multiselect-option.selected {
            background-color: #e7f3ff;
        }

        .option-checkbox {
            margin-right: 10px;
            cursor: pointer;
            width: 16px;
            height: 16px;
        }

        .option-text {
            flex-grow: 1;
            font-size: 14px;
            line-height: 1.4;
        }

        .option-code {
            font-weight: 600;
            color: #0d6efd;
        }

        .option-name {
            color: #495057;
            margin-left: 8px;
        }

        /* Counter Badge */
        .selection-counter {
            background: #0d6efd;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: bold;
            margin-left: 4px;
        }

        /* No Results Message */
        .no-results {
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-style: italic;
        }

        /* Scrollbar Styling */
        .options-section::-webkit-scrollbar {
            width: 6px;
        }

        .options-section::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .options-section::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .options-section::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .selected-item {
                max-width: 150px;
                font-size: 11px;
            }
            
            .multiselect-input {
                min-height: 38px;
            }
        }

        /* Demo Styling */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f5f7fa;
            padding: 20px;
        }

        .demo-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        .code-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            border-left: 4px solid #0d6efd;
        }

        .code-block {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin: 10px 0;
        }
</style>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
<script>
    document.addEventListener('DOMContentLoaded', function() {
            const multiselectInput = document.getElementById('multiselectInput');
            const dropdownContent = document.getElementById('dropdownContent');
            const dropdownArrow = document.getElementById('dropdownArrow');
            const searchInput = document.getElementById('ledgerSearch');
            const optionsContainer = document.getElementById('optionsContainer');
            const selectedItems = document.getElementById('selectedItems');
            const placeholderText = document.getElementById('placeholderText');
            const selectAllBtn = document.getElementById('selectAllBtn');
            const deselectAllBtn = document.getElementById('deselectAllBtn');
            const selectVisibleBtn = document.getElementById('selectVisibleBtn');

            // Toggle dropdown
            multiselectInput.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-item')) {
                    return; // Don't toggle dropdown when removing items
                }
                
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
                if (!e.target.closest('.enhanced-multiselect')) {
                    dropdownContent.style.display = 'none';
                    dropdownArrow.classList.remove('rotated');
                    multiselectInput.classList.remove('active');
                }
            });

            // Search functionality
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const options = optionsContainer.querySelectorAll('.multiselect-option');
                let visibleCount = 0;

                options.forEach(option => {
                    const text = option.querySelector('.option-text').textContent.toLowerCase();
                    const isVisible = text.includes(searchTerm);
                    option.style.display = isVisible ? 'flex' : 'none';
                    if (isVisible) visibleCount++;
                });

                // Show/hide no results message
                const existingNoResults = optionsContainer.querySelector('.no-results');
                if (existingNoResults) {
                    existingNoResults.remove();
                }

                if (visibleCount === 0 && searchTerm.length > 0) {
                    const noResultsDiv = document.createElement('div');
                    noResultsDiv.className = 'no-results';
                    noResultsDiv.innerHTML = '<i class="fas fa-search me-2"></i>No ledgers found matching "' + searchTerm + '"';
                    optionsContainer.appendChild(noResultsDiv);
                }
            });

            // Select All functionality
            selectAllBtn.addEventListener('click', function() {
                const allCheckboxes = optionsContainer.querySelectorAll('input[type="checkbox"]');
                allCheckboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
                updateSelectedDisplay();
            });

            // Deselect All functionality
            deselectAllBtn.addEventListener('click', function() {
                const allCheckboxes = optionsContainer.querySelectorAll('input[type="checkbox"]');
                allCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                updateSelectedDisplay();
            });

            // Select Visible functionality
            selectVisibleBtn.addEventListener('click', function() {
                const visibleCheckboxes = optionsContainer.querySelectorAll('.multiselect-option:not([style*="display: none"]) input[type="checkbox"]');
                visibleCheckboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
                updateSelectedDisplay();
            });

            // Update selected items display
            function updateSelectedDisplay() {
                const checkedBoxes = optionsContainer.querySelectorAll('input[type="checkbox"]:checked');
                selectedItems.innerHTML = '';

                if (checkedBoxes.length === 0) {
                    placeholderText.style.display = 'block';
                    multiselectInput.classList.remove('has-selections');
                } else {
                    placeholderText.style.display = 'none';
                    multiselectInput.classList.add('has-selections');

                    checkedBoxes.forEach((checkbox, index) => {
                        if (index < 3) { // Show only first 3 items
                            const optionText = checkbox.closest('.multiselect-option').querySelector('.option-text').textContent.trim();
                            const selectedItem = createSelectedItem(optionText, checkbox.value);
                            selectedItems.appendChild(selectedItem);
                        }
                    });

                    // Add counter if more than 3 items
                    if (checkedBoxes.length > 3) {
                        const counter = document.createElement('span');
                        counter.className = 'selection-counter';
                        counter.textContent = `+${checkedBoxes.length - 3} more`;
                        selectedItems.appendChild(counter);
                    }
                }

                // Update option checkboxes visual state
                optionsContainer.querySelectorAll('.multiselect-option').forEach(option => {
                    const checkbox = option.querySelector('input[type="checkbox"]');
                    option.classList.toggle('selected', checkbox.checked);
                });
            }

            function createSelectedItem(text, value) {
                const item = document.createElement('div');
                item.className = 'selected-item';
                
                const textSpan = document.createElement('span');
                textSpan.className = 'selected-item-text';
                textSpan.textContent = text.length > 25 ? text.substring(0, 25) + '...' : text;
                textSpan.title = text; // Full text on hover
                
                const removeBtn = document.createElement('span');
                removeBtn.className = 'remove-item';
                removeBtn.innerHTML = '×';
                removeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    removeSelectedItem(value);
                });
                
                item.appendChild(textSpan);
                item.appendChild(removeBtn);
                return item;
            }

            function removeSelectedItem(value) {
                const checkbox = optionsContainer.querySelector(`input[value="${value}"]`);
                if (checkbox) {
                    checkbox.checked = false;
                    updateSelectedDisplay();
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

            // Prevent dropdown from closing when interacting with content
            dropdownContent.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });

        // Demo function to show selected values
        function showSelectedValues() {
            const checkedBoxes = document.querySelectorAll('input[name="ledger_ids[]"]:checked');
            const output = document.getElementById('selectedOutput');
            
            if (checkedBoxes.length === 0) {
                output.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>No ledgers selected</div>';
            } else {
                let html = '<div class="alert alert-success"><h6><i class="fas fa-check-circle me-2"></i>Selected Ledgers:</h6><ul class="mb-0">';
                checkedBoxes.forEach(checkbox => {
                    const optionText = checkbox.closest('.multiselect-option').querySelector('.option-text').textContent.trim();
                    html += `<li><strong>ID:</strong> ${checkbox.value} - <strong>Name:</strong> ${optionText}</li>`;
                });
                html += '</ul></div>';
                output.innerHTML = html;
            }
        }
</script>
@endpush