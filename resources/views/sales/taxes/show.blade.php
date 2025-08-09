@extends('layouts.app')

@section('title', 'View Tax')

@section('content')
<div class="page-header">
    <h1 class="page-title">Tax Details</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sales.taxes.index') }}">Taxes</a></li>
            <li class="breadcrumb-item active">{{ $tax->name }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Tax Information</h5>
                <span class="badge bg-{{ $tax->status ? 'success' : 'danger' }} fs-6">
                    {{ $tax->status ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Tax Name</label>
                        <div class="fw-bold fs-5">{{ $tax->name }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Tax Rate</label>
                        <div class="fw-bold fs-5 text-primary">{{ $tax->percent }}%</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Applicable For</label>
                        <div>
                            <span class="badge bg-secondary fs-6">
                                {{ ucfirst(str_replace('_', ' ', $tax->applicable_for)) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Tax Liability Account</label>
                        <div class="fw-bold">{{ $tax->ledger->name ?? 'N/A' }}</div>
                        @if($tax->ledger)
                        <small class="text-muted">Account Code: {{ $tax->ledger->left_code ?? 'N/A' }}</small>
                        @endif
                    </div>
                </div>

                <!-- Tax Calculation Example -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h6>Tax Calculation Example</h6>
                        <div class="border rounded p-3 bg-light">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Exclusive Tax Calculation:</strong><br>
                                    Product/Service Price: ₹1,000.00<br>
                                    Tax ({{ $tax->percent }}%): ₹{{ number_format((1000 * $tax->percent) / 100, 2) }}<br>
                                    <strong class="text-success">Total Amount: ₹{{ number_format(1000 + ((1000 * $tax->percent) / 100), 2) }}</strong>
                                </div>
                                <div class="col-md-6">
                                    <strong>Calculation Formula:</strong><br>
                                    <code>Tax Amount = Base Amount × {{ $tax->percent }}%</code><br>
                                    <code>Total = Base Amount + Tax Amount</code><br>
                                    <small class="text-muted">
                                        Tax is calculated separately and added to the base amount.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage Statistics -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Usage Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="mb-2">
                            <i class="fas fa-file-invoice fa-2x text-primary"></i>
                        </div>
                        <h4>{{ DB::table('quotation_items')->where('tax_id', $tax->id)->count() }}</h4>
                        <small class="text-muted">Used in Quotations</small>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-2">
                            <i class="fas fa-file-invoice-dollar fa-2x text-success"></i>
                        </div>
                        <h4>{{ DB::table('sales_invoice_items')->where('tax_id', $tax->id)->count() }}</h4>
                        <small class="text-muted">Used in Invoices</small>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-2">
                            <i class="fas fa-calculator fa-2x text-info"></i>
                        </div>
                        <h4>₹{{ number_format(DB::table('sales_invoice_items')->where('tax_id', $tax->id)->sum('tax_amount'), 2) }}</h4>
                        <small class="text-muted">Total Tax Collected</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @can('sales.taxes.edit')
                    <a href="{{ route('sales.taxes.edit', $tax) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i> Edit Tax
                    </a>
                    @endcan

                    @can('sales.taxes.delete')
                    @if(DB::table('quotation_items')->where('tax_id', $tax->id)->count() == 0 && 
                        DB::table('sales_invoice_items')->where('tax_id', $tax->id)->count() == 0)
                    <form action="{{ route('sales.taxes.destroy', $tax) }}" method="POST" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i> Delete Tax
                        </button>
                    </form>
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Cannot delete tax as it's being used in transactions.
                    </div>
                    @endif
                    @endcan

                    <a href="{{ route('sales.taxes.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Audit Information -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Audit Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td>{{ $tax->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @if($tax->creator)
                    <tr>
                        <td><strong>Created by:</strong></td>
                        <td>{{ $tax->creator->name }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Last Updated:</strong></td>
                        <td>{{ $tax->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Related Information -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Related Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Ledger Group</label>
                    <div>{{ $tax->ledger->group->name ?? 'N/A' }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Display Name</label>
                    <div>{{ $tax->display_name }}</div>
                </div>
                @if($tax->applicable_for === 'product')
                <div class="alert alert-info">
                    <small>This tax can only be applied to products.</small>
                </div>
                @elseif($tax->applicable_for === 'service')
                <div class="alert alert-info">
                    <small>This tax can only be applied to services.</small>
                </div>
                @else
                <div class="alert alert-success">
                    <small>This tax can be applied to both products and services.</small>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Delete confirmation
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        
        if (confirm('Are you sure you want to delete this tax? This action cannot be undone.')) {
            form.submit();
        }
    });
});
</script>
@endsection