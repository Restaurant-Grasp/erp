@extends('layouts.app')

@section('title', 'Sales Payments')

@section('content')
<div class="page-header">
    <h1 class="page-title">Sales Payments</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sales.invoices.index') }}">Sales Invoices</a></li>
            <li class="breadcrumb-item active">Payments</li>
        </ol>
    </nav>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-credit-card fa-2x text-primary mb-2"></i>
                <h5 class="card-title">Total Payments</h5>
                <h3 id="totalPayments">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-calendar-day fa-2x text-success mb-2"></i>
                <h5 class="card-title">Today's Payments</h5>
                <h3 id="todayPayments">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-rupee-sign fa-2x text-warning mb-2"></i>
                <h5 class="card-title">Total Amount</h5>
                <h3 id="totalAmount">₹0.00</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                <h5 class="card-title">This Month</h5>
                <h3 id="monthAmount">₹0.00</h3>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Search & Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search invoice, customer..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="payment_mode">
                        <option value="">All Payment Modes</option>
                        <!-- Will be populated dynamically -->
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="received_by">
                        <option value="">All Users</option>
                        <!-- Will be populated dynamically -->
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Main Content -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Sales Payments</h5>
        <div>
            <button type="button" class="btn btn-success" onclick="exportPayments()">
                <i class="fas fa-download me-2"></i> Export
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="paymentsTable">
                <thead>
                    <tr>
                        <th>Payment Date</th>
                        <th>Invoice No</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Payment Mode</th>
                        <th>Received By</th>
                        <th>File</th>
                        <th>Created At</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Will be populated via AJAX -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div id="pagination-container" class="mt-3"></div>
    </div>
</div>

<!-- Payment Details Modal -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1" aria-labelledby="paymentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentDetailsModalLabel">Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="paymentDetailsContent">
                <!-- Payment details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Payment Modal -->
<div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPaymentModalLabel">Edit Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPaymentForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <!-- Edit form will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadPayments();
    loadFilterOptions();
    loadStatistics();
    
    // Auto-submit filter form on change
    $('#filterForm input, #filterForm select').on('change', function() {
        loadPayments();
    });
});

function loadPayments(page = 1) {
    const formData = new FormData(document.getElementById('filterForm'));
    formData.append('page', page);
    
    $.ajax({
        url: '/sales/payments/list',
        method: 'GET',
        data: Object.fromEntries(formData),
        success: function(data) {
            populatePaymentsTable(data.payments);
            updatePagination(data.pagination);
        },
        error: function(xhr) {
            console.error('Error loading payments:', xhr);
        }
    });
}

function populatePaymentsTable(payments) {
    const tbody = $('#paymentsTable tbody');
    tbody.empty();
    
    if (payments.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="9" class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-credit-card fa-3x mb-3"></i>
                        <p>No payments found</p>
                    </div>
                </td>
            </tr>
        `);
        return;
    }
    
    payments.forEach(payment => {
        const fileLink = payment.file_upload ? 
            `<a href="${payment.file_url}" target="_blank" class="btn btn-sm btn-outline-info" title="Download File">
                <i class="fas fa-download"></i>
            </a>` : 
            '<span class="text-muted">-</span>';
        
        const accountMigrationBadge = payment.account_migration ? 
            '<span class="badge bg-success ms-2" title="Accounting entries created">Migrated</span>' : 
            '<span class="badge bg-warning ms-2" title="Pending accounting entries">Pending</span>';
        
        tbody.append(`
            <tr>
                <td>${payment.payment_date}</td>
                <td>
                    <a href="/sales/invoices/${payment.invoice_id}" class="text-decoration-none">
                        ${payment.invoice_no}
                    </a>
                </td>
                <td>${payment.customer_name}</td>
                <td>
                    <strong>₹${payment.paid_amount}</strong>
                    ${accountMigrationBadge}
                </td>
                <td>
                    ${payment.payment_mode}
                    <br><small class="text-muted">${payment.ledger_name}</small>
                </td>
                <td>${payment.received_by}</td>
                <td class="text-center">${fileLink}</td>
                <td>
                    ${payment.created_at}
                    <br><small class="text-muted">by ${payment.created_by}</small>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewPayment(${payment.id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="editPayment(${payment.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deletePayment(${payment.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `);
    });
}

function loadFilterOptions() {
    // Load payment modes
    $.get('/payment-modes/active-payment-modes')
        .done(function(data) {
            const select = $('select[name="payment_mode"]');
            data.forEach(mode => {
                select.append(`<option value="${mode.id}">${mode.name}</option>`);
            });
        });
    
    // Load users
    $.get('/sales/payments/users')
        .done(function(data) {
            const select = $('select[name="received_by"]');
            data.forEach(user => {
                select.append(`<option value="${user.id}">${user.name}</option>`);
            });
        });
}

function loadStatistics() {
    $.get('/sales/payments/statistics')
        .done(function(data) {
            $('#totalPayments').text(data.total_payments);
            $('#todayPayments').text(data.today_payments);
            $('#totalAmount').text('₹' + parseFloat(data.total_amount).toFixed(2));
            $('#monthAmount').text('₹' + parseFloat(data.month_amount).toFixed(2));
        });
}

function viewPayment(paymentId) {
    $.get(`/sales/payments/${paymentId}`)
        .done(function(payment) {
            const accountMigrationStatus = payment.account_migration ? 
                '<span class="badge bg-success">Migrated</span>' : 
                '<span class="badge bg-warning">Pending</span>';
            
            const fileSection = payment.file_upload ? 
                `<div class="col-md-12">
                    <label class="form-label">Uploaded File:</label>
                    <div>
                        <a href="${payment.file_url}" target="_blank" class="btn btn-outline-info">
                            <i class="fas fa-download me-2"></i>Download File
                        </a>
                    </div>
                </div>` : '';
            
            $('#paymentDetailsContent').html(`
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Invoice No:</label>
                        <div class="fw-bold">${payment.invoice.invoice_no}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Customer:</label>
                        <div class="fw-bold">${payment.invoice.customer_name}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Payment Date:</label>
                        <div>${payment.payment_date}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Amount:</label>
                        <div class="fw-bold text-success">₹${payment.paid_amount}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Account Migration:</label>
                        <div>${accountMigrationStatus}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Mode:</label>
                        <div>${payment.payment_mode.name} (${payment.payment_mode.ledger.name})</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Received By:</label>
                        <div>${payment.received_by.name}</div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Notes:</label>
                        <div>${payment.notes || 'No notes'}</div>
                    </div>
                    ${fileSection}
                    <div class="col-md-6">
                        <label class="form-label">Created By:</label>
                        <div>${payment.created_by.name}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Created At:</label>
                        <div>${payment.created_at}</div>
                    </div>
                </div>
            `);
            
            $('#paymentDetailsModal').modal('show');
        })
        .fail(function(xhr) {
            alert('Error loading payment details: ' + xhr.responseJSON.message);
        });
}

function editPayment(paymentId) {
    // Implementation for edit payment
    alert('Edit payment functionality will be implemented based on your requirements');
}

function deletePayment(paymentId) {
    if (confirm('Are you sure you want to delete this payment? This action cannot be undone.')) {
        $.ajax({
            url: `/sales/payments/${paymentId}`,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
        .done(function(data) {
            alert('Payment deleted successfully!');
            loadPayments();
            loadStatistics();
        })
        .fail(function(xhr) {
            alert('Error: ' + xhr.responseJSON.message);
        });
    }
}

function exportPayments() {
    const formData = new FormData(document.getElementById('filterForm'));
    formData.append('export', '1');
    
    // Create a temporary form to submit for file download
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = '/sales/payments/export';
    
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function updatePagination(pagination) {
    // Implementation for pagination
    $('#pagination-container').html(pagination.links);
}
</script>
@endsection
