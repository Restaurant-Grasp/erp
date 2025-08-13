@extends('layouts.app')

@section('title', 'Sales Invoices')

@section('content')
<div class="page-header">
    <h1 class="page-title">Sales Invoices</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Sales Invoices</li>
        </ol>
    </nav>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-file-invoice-dollar fa-2x text-primary mb-2"></i>
                <h5 class="card-title">Total Invoices</h5>
                <h3>{{ $statistics['total_invoices'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-rupee-sign fa-2x text-success mb-2"></i>
                <h5 class="card-title">Total Revenue</h5>
                <h3>₹{{ number_format($statistics['total_amount'] ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                <h5 class="card-title">Outstanding</h5>
                <h3>₹{{ number_format($statistics['outstanding_amount'] ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                <h5 class="card-title">Overdue</h5>
                <h3>₹{{ number_format($statistics['overdue_amount'] ?? 0, 2) }}</h3>
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
        <form method="GET" action="{{ route('sales.invoices.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search invoices..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="due_status">
                        <option value="">All Due Status</option>
                        <option value="overdue" {{ request('due_status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="due_today" {{ request('due_status') == 'due_today' ? 'selected' : '' }}>Due Today</option>
                        <option value="due_this_week" {{ request('due_status') == 'due_this_week' ? 'selected' : '' }}>Due This Week</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
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
        <h5 class="mb-0">All Invoices</h5>
        <div>
            @php
            $role = auth()->user()->getRoleNames()->first();
            $permissions = getCurrentRolePermissions($role);
            @endphp
            @if ($permissions->contains('name', 'sales.invoices.create'))
            <a href="{{ route('sales.invoices.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Create Invoice
            </a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Delivery</th>
                        <th width="250">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr class="{{ $invoice->is_overdue ? 'table-danger' : '' }}">
                        <td>
                            <a href="{{ route('sales.invoices.show', $invoice) }}" class="text-decoration-none">
                                {{ $invoice->invoice_no }}
                            </a>
                            @if($invoice->quotation_id)
                            <br><small class="text-muted">From Quotation</small>
                            @endif
                        </td>
                        <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                        <td>
                            <strong>{{ $invoice->customer->company_name }}</strong>
                            @if($invoice->customer->city)
                            <br><small class="text-muted">{{ $invoice->customer->city }}</small>
                            @endif
                        </td>
                        <td>
                            {{ $invoice->due_date->format('d/m/Y') }}
                            @if($invoice->is_overdue)
                            <br><small class="text-danger">
                                {{ $invoice->due_date->diffForHumans() }}
                            </small>
                            @endif
                        </td>
                        <td>₹{{ number_format($invoice->total_amount, 2) }}</td>
                        <td>₹{{ number_format($invoice->paid_amount, 2) }}</td>
                        <td>
                            @if($invoice->balance_amount > 0)
                            <span class="text-danger">₹{{ number_format($invoice->balance_amount, 2) }}</span>
                            @else
                            <span class="text-success">₹0.00</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $invoice->status_badge }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $invoice->delivery_status_badge }}">
                                {{ ucfirst(str_replace('_', ' ', $invoice->delivery_status)) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                @if ($permissions->contains('name', 'sales.invoices.view'))
                                <a href="{{ route('sales.invoices.show', $invoice) }}"
                                    class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endif

                                @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                                @if ($permissions->contains('name', 'sales.invoices.edit'))
                                <a href="{{ route('sales.invoices.edit', $invoice) }}"
                                    class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                @endif

                                <!-- Payment Button - Show only for pending, partial, overdue -->
                                @if(in_array($invoice->status, ['pending', 'partial', 'overdue']) && $permissions->contains('name', 'sales.payments.create'))
                                <button type="button" class="btn btn-sm btn-outline-success" 
                                        onclick="openPaymentModal({{ $invoice->id }})" title="Add Payment">
                                    <i class="fas fa-credit-card"></i>
                                </button>
                                @endif

                                <!-- View Payments Button -->
                                @if($invoice->paid_amount > 0 && $permissions->contains('name', 'sales.payments.view'))
                                <button type="button" class="btn btn-sm btn-outline-info" 
                                        onclick="viewPayments({{ $invoice->id }})" title="View Payments">
                                    <i class="fas fa-list"></i>
                                </button>
                                @endif

                                @if($invoice->delivery_status !== 'delivered')
                                <a href="{{ route('sales.delivery-orders.create', ['invoice_id' => $invoice->id]) }}"
                                    class="btn btn-sm btn-outline-info" title="Create Delivery Order">
                                    <i class="fas fa-truck"></i>
                                </a>
                                @endif

                                @if ($permissions->contains('name', 'sales.invoices.pdf'))
                                <a href="{{ route('sales.invoices.pdf', $invoice) }}"
                                    class="btn btn-sm btn-outline-secondary" title="PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                @endif

                                @if($invoice->can_be_cancelled)
                                @if ($permissions->contains('name', 'sales.invoices.cancel'))
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="cancelInvoice({{ $invoice->id }})" title="Cancel">
                                    <i class="fas fa-ban"></i>
                                </button>
                                @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-file-invoice-dollar fa-3x mb-3"></i>
                                <p>No invoices found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        {{ $invoices->withQueryString()->links() }}
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Add Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="paymentForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Invoice No:</label>
                            <span id="modalInvoiceNo" class="fw-bold"></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Customer:</label>
                            <span id="modalCustomerName" class="fw-bold"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Total Amount:</label>
                            <span id="modalTotalAmount" class="fw-bold text-primary"></span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Paid Amount:</label>
                            <span id="modalPaidAmount" class="fw-bold text-success"></span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Balance Amount:</label>
                            <span id="modalBalanceAmount" class="fw-bold text-danger"></span>
                        </div>
                    </div>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Paid Amount <span class="text-danger">*</span></label>
                            <input type="number" name="paid_amount" class="form-control" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_mode_id" class="form-select" required>
                                <option value="">Select Payment Mode</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Received By <span class="text-danger">*</span></label>
                            <select name="received_by" class="form-select" required>
                                <option value="">Select User</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Upload File (Optional)</label>
                            <input type="file" name="file_upload" class="form-control" 
                                   accept=".jpeg,.png,.jpg,.gif,.pdf,.docx,.doc">
                            <small class="text-muted">Supported formats: Images, PDF, DOCX, DOC (Max: 10MB)</small>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Payment notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Payments Modal -->
<div class="modal fade" id="viewPaymentsModal" tabindex="-1" aria-labelledby="viewPaymentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewPaymentsModalLabel">Payment History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Invoice No:</label>
                        <span id="viewModalInvoiceNo" class="fw-bold"></span>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Customer:</label>
                        <span id="viewModalCustomerName" class="fw-bold"></span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped" id="paymentsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Payment Mode</th>
                                <th>Received By</th>
                                <th>Notes</th>
                                <th>File</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Payments will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Invoice Modal -->
<div class="modal fade" id="cancelInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelInvoiceForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Cancellation Reason <span class="text-danger">*</span></label>
                        <textarea name="cancellation_reason" class="form-control" rows="3" required
                            placeholder="Please provide reason for cancellation..."></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action cannot be undone. The invoice will be marked as cancelled.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Cancel Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Auto-submit filter form on change
    $('#filterForm select').on('change', function() {
        $('#filterForm').submit();
    });
});

let currentInvoiceId = null;

function openPaymentModal(invoiceId) {
    let createPaymentUrl = `/sales/invoices/${invoiceId}/payments/create`;

    // Fetch payment form data
    $.get(createPaymentUrl)
    .done(function(data) {
            // Populate invoice details
            $('#modalInvoiceNo').text(data.invoice.invoice_no);
            $('#modalCustomerName').text(data.invoice.customer_name);
            $('#modalTotalAmount').text('₹' + parseFloat(data.invoice.total_amount).toFixed(2));
            $('#modalPaidAmount').text('₹' + parseFloat(data.invoice.paid_amount).toFixed(2));
            $('#modalBalanceAmount').text('₹' + parseFloat(data.invoice.balance_amount).toFixed(2));
            
            // Set max amount for payment
            $('input[name="paid_amount"]').attr('max', data.invoice.balance_amount);
            
            // Populate payment modes
            const paymentModeSelect = $('select[name="payment_mode_id"]');
            paymentModeSelect.empty().append('<option value="">Select Payment Mode</option>');
            data.payment_modes.forEach(mode => {
                paymentModeSelect.append(`<option value="${mode.id}">${mode.name} (${mode.ledger_name})</option>`);
            });
            
            // Populate users
            const userSelect = $('select[name="received_by"]');
            userSelect.empty().append('<option value="">Select User</option>');
            data.users.forEach(user => {
                userSelect.append(`<option value="${user.id}">${user.name}</option>`);
            });
            
            // Reset form
            $('#paymentForm')[0].reset();
            $('input[name="payment_date"]').val(new Date().toISOString().split('T')[0]);
            
            // Show modal
            $('#paymentModal').modal('show');
        })
        .fail(function(xhr) {
            alert('Error loading payment form: ' + xhr.responseJSON.error);
        });
}

function viewPayments(invoiceId) {
    $.get(`/sales/invoices/${invoiceId}/payments`)
        .done(function(data) {
            // Populate invoice details
            $('#viewModalInvoiceNo').text(data.invoice.invoice_no);
            $('#viewModalCustomerName').text(data.invoice.customer_name);
            
            // Populate payments table
            const tbody = $('#paymentsTable tbody');
            tbody.empty();
            
            if (data.payments.length === 0) {
                tbody.append(`
                    <tr>
                        <td colspan="7" class="text-center text-muted">No payments found</td>
                    </tr>
                `);
            } else {
                data.payments.forEach(payment => {
                    const fileLink = payment.file_upload ? 
                        `<a href="${payment.file_url}" target="_blank" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-download"></i>
                        </a>` : '-';
                    
                    tbody.append(`
                        <tr>
                            <td>${payment.payment_date}</td>
                            <td>₹${payment.paid_amount}</td>
                            <td>${payment.payment_mode}<br><small class="text-muted">${payment.ledger_name}</small></td>
                            <td>${payment.received_by}</td>
                            <td>${payment.notes || '-'}</td>
                            <td>${fileLink}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="editPayment(${payment.id})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deletePayment(${payment.id})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
            }
            
            $('#viewPaymentsModal').modal('show');
        })
        .fail(function(xhr) {
            alert('Error loading payments: ' + xhr.responseJSON.error);
        });
}

// Handle payment form submission
$('#paymentForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    $.ajax({
        url: `/sales/invoices/${currentInvoiceId}/payments`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .done(function(data) {
        $('#paymentModal').modal('hide');
        alert('Payment recorded successfully!');
        location.reload(); // Refresh page to show updated amounts
    })
    .fail(function(xhr) {
        const errors = xhr.responseJSON.errors;
        if (errors) {
            let errorMessage = 'Validation errors:\n';
            Object.keys(errors).forEach(key => {
                errorMessage += `${key}: ${errors[key].join(', ')}\n`;
            });
            alert(errorMessage);
        } else {
            alert('Error: ' + xhr.responseJSON.error);
        }
    });
});

function editPayment(paymentId) {
    // Implementation for edit payment
    alert('Edit payment functionality to be implemented');
}

function deletePayment(paymentId) {
    if (confirm('Are you sure you want to delete this payment?')) {
        $.ajax({
            url: `/sales/invoices/${currentInvoiceId}/payments/${paymentId}`,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
        .done(function(data) {
            alert('Payment deleted successfully!');
            $('#viewPaymentsModal').modal('hide');
            location.reload();
        })
        .fail(function(xhr) {
            alert('Error: ' + xhr.responseJSON.error);
        });
    }
}

function cancelInvoice(invoiceId) {
    const modal = new bootstrap.Modal(document.getElementById('cancelInvoiceModal'));
    const form = document.getElementById('cancelInvoiceForm');
    form.action = `/sales/invoices/${invoiceId}/cancel`;
    modal.show();
}
</script>
@endsection