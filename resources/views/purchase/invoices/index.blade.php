@extends('layouts.app')

@section('title', 'Purchase Invoices')

@section('content')
<div class="page-header">
    <h1 class="page-title">Purchase Invoices</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Purchase Invoices</li>
        </ol>
    </nav>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Search & Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('purchase.invoices.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search Invoice No, Vendor, PO..." 
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
                    <select class="form-select" name="invoice_type">
                        <option value="">All Types</option>
                        <option value="direct" {{ request('invoice_type') == 'direct' ? 'selected' : '' }}>Direct</option>
                        <option value="po_conversion" {{ request('invoice_type') == 'po_conversion' ? 'selected' : '' }}>PO Conversion</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="vendor">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ request('vendor') == $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->company_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_from" placeholder="From Date" 
                           value="{{ request('date_from') }}">
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
        <h5 class="mb-0">All Purchase Invoices</h5>
        <div>
                 @php
            $role = auth()->user()->getRoleNames()->first();
            $permissions = getCurrentRolePermissions($role);
            @endphp
            @if ($permissions->contains('name', 'purchases.invoices.create'))
        
            <a href="{{ route('purchase.invoices.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Create Direct Invoice
            </a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="120">Invoice No</th>
                        <th>Vendor</th>
                        <th width="100">Invoice Date</th>
                        <th width="100">Due Date</th>
                        <th width="120">Total Amount</th>
                        <th width="100">Status</th>
                        <th width="80">Type</th>
                        <th width="100">Received %</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td>
                            <a href="{{ route('purchase.invoices.show', $invoice) }}" class="text-decoration-none">
                                {{ $invoice->invoice_no }}
                            </a>
                            @if($invoice->vendor_invoice_no)
                            <br><small class="text-muted">Vendor: {{ $invoice->vendor_invoice_no }}</small>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $invoice->vendor->company_name }}</strong>
                            <br><small class="text-muted">{{ $invoice->vendor->vendor_code }}</small>
                            @if($invoice->purchaseOrder)
                            <br><small class="text-info">PO: {{ $invoice->purchaseOrder->po_no }}</small>
                            @endif
                        </td>
                        <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                        <td>
                            {{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}
                            @if($invoice->status === 'overdue')
                            <br><small class="text-danger">{{ $invoice->days_overdue }} days overdue</small>
                            @endif
                        </td>
                        <td>
                            {{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}
                            @if($invoice->balance_amount > 0)
                            <br><small class="text-warning">Bal: {{ number_format($invoice->balance_amount, 2) }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $invoice->status_badge }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $invoice->invoice_type_badge }}">
                                {{ $invoice->invoice_type === 'po_conversion' ? 'PO Conv' : 'Direct' }}
                            </span>
                        </td>
                        <td>
                            @if($invoice->received_percentage > 0)
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-info" role="progressbar" 
                                         style="width: {{ $invoice->received_percentage }}%"
                                         aria-valuenow="{{ $invoice->received_percentage }}" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        {{ number_format($invoice->received_percentage, 1) }}%
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">0%</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                 @if ($permissions->contains('name', 'purchases.invoices.view'))
                          
                                <a href="{{ route('purchase.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary" 
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endif
                                 @if ($permissions->contains('name', 'purchases.invoices.edit'))

                            
                                @if(in_array($invoice->status, ['draft', 'pending']))
                                <a href="{{ route('purchase.invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-primary" 
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                @endif

                                <!-- Payment Button - Show only for pending, partial, overdue -->
                                @if(in_array($invoice->status, ['pending', 'partial', 'overdue']) && $permissions->contains('name', 'purchases.payments.create'))
                                
                                <a href="{{ route('purchases.payments.create', $invoice->id) }}" 
   class="btn btn-sm btn-outline-success" title="Add Payment">
    <i class="fas fa-credit-card"></i>
</a>

                                @endif

                                <!-- View Payments Button -->
                                @if($invoice->paid_amount > 0 && $permissions->contains('name', 'purchases.payments.view'))
                                <button type="button" class="btn btn-sm btn-outline-info" 
                                        onclick="viewPayments({{ $invoice->id }})" title="View Payments">
                                    <i class="fas fa-list"></i>
                                </button>
                                @endif

                               @if ($permissions->contains('name', 'purchases.grn.create'))

                            
                                @if($invoice->received_percentage < 100)
                                <a href="{{ route('purchase.grn.create-from-invoice', $invoice) }}" class="btn btn-sm btn-outline-success" 
                                   title="Create GRN">
                                    <i class="fas fa-truck-loading"></i>
                                </a>
                                @endif
                                @endif
                                       @if ($permissions->contains('name', 'purchases.invoices.delete'))
                         
                                @if(in_array($invoice->status, ['draft', 'pending']))
                                <form action="{{ route('purchase.invoices.destroy', $invoice) }}" method="POST" 
                                      class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-file-invoice fa-3x mb-3"></i>
                                <p>No purchase invoices found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div>
                Showing {{ $invoices->firstItem() ?? 0 }} to {{ $invoices->lastItem() ?? 0 }} 
                of {{ $invoices->total() }} entries
            </div>
            
            {{ $invoices->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<!-- Purchase Invoice Statistics -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-file-invoice fa-2x text-primary mb-2"></i>
                <h5 class="card-title">Total Invoices</h5>
                <h3>{{ $invoices->total() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                <h5 class="card-title">Pending</h5>
                <h3>{{ \App\Models\PurchaseInvoice::where('status', 'pending')->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                <h5 class="card-title">Overdue</h5>
                <h3>{{ \App\Models\PurchaseInvoice::where('status', 'overdue')->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h5 class="card-title">Paid</h5>
                <h3>{{ \App\Models\PurchaseInvoice::where('status', 'paid')->count() }}</h3>
            </div>
        </div>
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
                            <label class="form-label">Vendor:</label>
                            <span id="modalVendorName" class="fw-bold"></span>
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
                        <label class="form-label">Vendor:</label>
                        <span id="viewModalVendorName" class="fw-bold"></span>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Delete confirmation
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        
        if (confirm('Are you sure you want to delete this purchase invoice? This action cannot be undone.')) {
            form.submit();
        }
    });

    // Auto-submit filter form on change
    $('#filterForm select').on('change', function() {
        $('#filterForm').submit();
    });
});

let currentInvoiceId = null;

function openPaymentModal(invoiceId) {
    currentInvoiceId = invoiceId;
    
    // Fetch payment form data
    $.get(`/purchase/invoices/${invoiceId}/payments/create`)
        .done(function(data) {
            // Populate invoice details
            $('#modalInvoiceNo').text(data.invoice.invoice_no);
            $('#modalVendorName').text(data.invoice.vendor_name);
            $('#modalTotalAmount').text('MYR ' + parseFloat(data.invoice.total_amount).toFixed(2));
            $('#modalPaidAmount').text('MYR ' + parseFloat(data.invoice.paid_amount).toFixed(2));
            $('#modalBalanceAmount').text('MYR ' + parseFloat(data.invoice.balance_amount).toFixed(2));
            
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
    $.get(`/purchase/invoices/${invoiceId}/payments`)
        .done(function(data) {
            // Populate invoice details
            $('#viewModalInvoiceNo').text(data.invoice.invoice_no);
            $('#viewModalVendorName').text(data.invoice.vendor_name);
            
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
                            <td>MYR ${payment.paid_amount}</td>
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
        url: `/purchase/invoices/${currentInvoiceId}/payments`,
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
            url: `/purchase/invoices/${currentInvoiceId}/payments/${paymentId}`,
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
</script>
@endsection