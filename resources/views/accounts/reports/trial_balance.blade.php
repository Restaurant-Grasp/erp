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
                <li class="breadcrumb-item active" aria-current="page">Trial Balance</li>
            </ol>
        </nav>

        <!-- Main Panel -->
        <div class="card shadow-sm">
            <!-- Header -->
            <div class="card-header bg-primary text-white">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">
                            <i class="fas fa-balance-scale me-2"></i>Trial Balance Report
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
                <form method="GET" action="{{ route('accounts.reports.trial-balance') }}" id="tbReportForm">
                    <div class="row g-3 align-items-end">
                        <!-- From Date -->
                        <div class="col-md-3">
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
                        <div class="col-md-3">
                            <label class="form-label">
                                To Date
                                <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="to_date" class="form-control"
                                value="{{ $toDate }}"
                                required>
                        </div>

                        <!-- Generate Button -->
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100" id="generateBtn">
                                <i class="fas fa-search me-1"></i>Generate Report
                            </button>
                        </div>

                        <!-- Export Buttons -->
                        <div class="col-md-3">
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="printReport()" title="Print Report">
                                    <i class="fas fa-print"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="exportReport('pdf')" title="Export to PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="exportReport('excel')" title="Export to Excel">
                                    <i class="fas fa-file-excel"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @if(!empty($trialBalanceData))


            <!-- Report Content -->
            <div class="card-body">
                <!-- Trial Balance Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th width="10%">Code</th>
                                <th width="40%">Account Name</th>
                                <th width="12.5%" class="text-end">Opening Debit</th>
                                <th width="12.5%" class="text-end">Opening Credit</th>
                                <th width="12.5%" class="text-end">Closing Debit</th>
                                <th width="12.5%" class="text-end">Closing Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="p-0">
                                    <div class="accordion" id="trialBalanceAccordion">
                                        @foreach($trialBalanceData as $parentIndex => $parentGroup)
                                        <div class="accordion-item border-0">
                                            <h2 class="accordion-header" id="heading{{ $parentIndex }}">
                                                <button class="accordion-button {{ $parentIndex != 0 ? 'collapsed' : '' }} trial-balance-header"
                                                    type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#collapse{{ $parentIndex }}"
                                                    aria-expanded="{{ $parentIndex == 0 ? 'true' : 'false' }}"
                                                    aria-controls="collapse{{ $parentIndex }}">
                                                    <div class="d-flex justify-content-between w-100 me-3">
                                                        <span>

                                                            {{ $parentGroup['code'] }} - {{ $parentGroup['name'] }}
                                                        </span>
                                                        <span class="text-end">
                                                            Dr: {{ number_format($parentGroup['totalClosingDebit'], 2) }}
                                                            | Cr: {{ number_format($parentGroup['totalClosingCredit'], 2) }}</
                                                                </span>
                                                    </div>
                                                </button>
                                            </h2>

                                            <div id="collapse{{ $parentIndex }}"
                                                class="accordion-collapse collapse {{ $parentIndex == 0 ? 'show' : '' }}"
                                                aria-labelledby="heading{{ $parentIndex }}"
                                                data-bs-parent="#trialBalanceAccordion">
                                                <div class="accordion-body p-0">
                                                    <table class="table mb-0">
                                                        <tbody>
                                                            @include('accounts.reports.partials.trial_balance_group', [
                                                            'group' => $parentGroup,
                                                            'level' => 0,
                                                            'fromDate' => $fromDate,
                                                            'toDate' => $toDate,
                                                            'activeYear' => $activeYear
                                                            ])
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="table-{{ $isBalanced ? 'success' : 'danger' }}">
                            <tr>
                                <th colspan="2" class="text-end fw-bold">
                                    <i class="fas fa-calculator me-2"></i>Grand Total
                                </th>
                                <th class="text-end fw-bold">₹{{ number_format($grandTotalOpeningDebit, 2) }}</th>
                                <th class="text-end fw-bold">₹{{ number_format($grandTotalOpeningCredit, 2) }}</th>
                                <th class="text-end fw-bold">₹{{ number_format($grandTotalClosingDebit, 2) }}</th>
                                <th class="text-end fw-bold">₹{{ number_format($grandTotalClosingCredit, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @else
            <!-- No Data State -->
            <div class="card-body text-center py-5">
                <div class="text-muted">
                    <i class="fas fa-balance-scale fa-3x mb-3 opacity-50"></i>
                    <h5>No Data Found</h5>
                    <p>Please select date range and generate the trial balance report.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    /* Enhanced Accordion Styling */
    .accordion-item {
        border: none !important;
        margin-bottom: 2px;
    }

    .trial-balance-header {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        color: #333;
        font-size: 14px;
        padding: 12px 16px;
    }

    .trial-balance-header:not(.collapsed) {
        background-color: #e7f3ff;
        border-color: #b6d7ff;
        box-shadow: none;
    }

    .trial-balance-header:focus {
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .trial-balance-header:hover {
        background-color: #e9ecef;
    }

    .trial-balance-header:not(.collapsed):hover {
        background-color: #d4edda;
    }


    /* Row Styling */
    .group-row {
        background-color: #f8f9fa !important;
        font-weight: 600;
        border-left: 4px solid #0d6efd;
    }

    .group-row:hover {
        background-color: #e9ecef !important;
    }

    .ledger-row {
        font-weight: normal;
        transition: background-color 0.15s ease;
    }

    .ledger-row:hover {
        background-color: #f8f9fa !important;
    }

    /* Ledger Link Styles */
    .ledger-link {
        color: #0d6efd;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.15s ease;
    }

    .ledger-link:hover {
        color: #0a58ca;
        text-decoration: underline;
    }

    .ledger-link::after {
        content: " \f35d";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        font-size: 0.8em;
        margin-left: 4px;
        opacity: 0.6;
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

    /* Enhanced Card Styling */
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .card-header {
        background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
        border-bottom: none;
    }

    /* Badge Enhancements */
    .badge {
        font-size: 0.75em;
        padding: 0.35em 0.65em;
    }

    /* Alert Enhancements */
    .alert {
        border: none;
        border-radius: 8px;
    }

    .alert-success {
        background-color: #d1e7dd;
        color: #0a3622;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #58151c;
    }

    /* Summary Cards */
    .card.border-0.bg-light {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card.border-0.bg-light:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    /* Print Styles */
    @media print {

        .card-header,
        .btn-group,
        .controls,
        .bg-light {
            display: none !important;
        }

        .accordion-button {
            background-color: transparent !important;
            border: none !important;
        }

        .accordion-collapse {
            display: block !important;
        }
    }

    .table thead th {
        color: black;
        background-color: #ffffffff;
        border-bottom: 2px black;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .btn-group {
            flex-direction: column;
            width: 100%;
        }

        .btn-group .btn {
            margin-bottom: 5px;
            border-radius: 0.375rem !important;
        }

        .table-responsive {
            font-size: 0.875rem;
        }

        .trial-balance-header {
            font-size: 12px;
            padding: 8px 12px;
        }

        .trial-balance-header .badge {
            font-size: 0.65em;
        }
    }

    /* Animation for expand/collapse all */
    .expanding {
        animation: expandAnimation 0.3s ease;
    }

    .collapsing {
        animation: collapseAnimation 0.3s ease;
    }

    @keyframes expandAnimation {
        from {
            opacity: 0.5;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes collapseAnimation {
        from {
            opacity: 1;
        }

        to {
            opacity: 0.5;
        }
    }
</style>

<script>
    // Enhanced JavaScript functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Form submission with loading state
        const generateBtn = document.getElementById('generateBtn');
        const form = document.getElementById('tbReportForm');

        form.addEventListener('submit', function() {
            generateBtn.classList.add('loading');
            generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating...';
        });
    });

    // Export functions with enhanced error handling
    function exportReport(type) {
        try {
            const form = document.getElementById('tbReportForm');
            const formData = new FormData(form);

            let url = '{{ route("accounts.reports.trial-balance") }}?';
            for (let [key, value] of formData.entries()) {
                url += key + '=' + encodeURIComponent(value) + '&';
            }
            url += 'export=' + type;

            // Show loading indicator
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            // Reset button after delay
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);

            window.location.href = url;
        } catch (error) {
            console.error('Export error:', error);
            alert('Error occurred while exporting. Please try again.');
        }
    }

    function printReport() {
        try {
            const form = document.getElementById('tbReportForm');
            const formData = new FormData(form);

            let url = '{{ route("accounts.reports.trial-balance") }}?';
            for (let [key, value] of formData.entries()) {
                url += key + '=' + encodeURIComponent(value) + '&';
            }
            url += 'export=print';

            window.open(url, '_blank');
        } catch (error) {
            console.error('Print error:', error);
            alert('Error occurred while printing. Please try again.');
        }
    }

    // Enhanced expand/collapse functionality
    function expandAll() {
        const accordionItems = document.querySelectorAll('#trialBalanceAccordion .accordion-collapse');
        accordionItems.forEach(item => {
            item.classList.add('expanding');
            item.classList.add('show');
            const button = document.querySelector(`[data-bs-target="#${item.id}"]`);
            if (button) {
                button.classList.remove('collapsed');
                button.setAttribute('aria-expanded', 'true');
            }

            setTimeout(() => {
                item.classList.remove('expanding');
            }, 300);
        });
    }

    function collapseAll() {
        const accordionItems = document.querySelectorAll('#trialBalanceAccordion .accordion-collapse');
        accordionItems.forEach(item => {
            item.classList.add('collapsing');
            item.classList.remove('show');
            const button = document.querySelector(`[data-bs-target="#${item.id}"]`);
            if (button) {
                button.classList.add('collapsed');
                button.setAttribute('aria-expanded', 'false');
            }

            setTimeout(() => {
                item.classList.remove('collapsing');
            }, 300);
        });
    }

    /**
     * Open ledger details in general ledger from trial balance
     */
    function openTrialBalanceLedgerReport(ledgerId, ledgerName) {
        try {
            const fromDate = document.querySelector('input[name="from_date"]').value || '{{ $fromDate }}';
            const toDate = document.querySelector('input[name="to_date"]').value || '{{ $toDate }}';

            // Build the general ledger URL with date filters, ledger ID, and invoice type
            let url = '{{ route("accounts.reports.general-ledger") }}';
            url += '?ledger_ids[]=' + encodeURIComponent(ledgerId);
            url += '&from_date=' + encodeURIComponent(fromDate);
            url += '&to_date=' + encodeURIComponent(toDate);
            url += '&invoice_type=all';

            // Show loading indicator for the clicked link
            const clickedLink = event.target;
            const originalText = clickedLink.innerHTML;
            clickedLink.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';

            // Open in new tab
            window.open(url, '_blank');

            // Reset link text
            setTimeout(() => {
                clickedLink.innerHTML = originalText;
            }, 1000);

        } catch (error) {
            console.error('Error opening ledger report:', error);
            alert('Error occurred while opening ledger report. Please try again.');
        }
    }

    // Enhanced keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey) {
            switch (e.key) {
                case 'e':
                    e.preventDefault();
                    expandAll();
                    break;
                case 'c':
                    e.preventDefault();
                    collapseAll();
                    break;
                case 'p':
                    e.preventDefault();
                    printReport();
                    break;
            }
        }
    });

    // Add tooltips for keyboard shortcuts
    document.addEventListener('DOMContentLoaded', function() {
        // Add title attributes for keyboard shortcuts
        const expandBtn = document.querySelector('[onclick="expandAll()"]');
        const collapseBtn = document.querySelector('[onclick="collapseAll()"]');
        const printBtn = document.querySelector('[onclick="printReport()"]');

        if (expandBtn) expandBtn.title = 'Expand All (Ctrl+E)';
        if (collapseBtn) collapseBtn.title = 'Collapse All (Ctrl+C)';
        if (printBtn) printBtn.title = 'Print Report (Ctrl+P)';
    });
</script>
@endsection