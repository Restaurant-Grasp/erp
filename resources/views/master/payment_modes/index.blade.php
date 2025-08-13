@extends('layouts.app')

@section('title', 'Payment Modes')

@section('content')
<div class="page-header">
    <h1 class="page-title">Payment Modes</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Payment Modes</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Payment Modes</h5>
        @php
        $role = auth()->user()->getRoleNames()->first();
        $permissions = getCurrentRolePermissions($role);
        @endphp
        @if ($permissions->contains('name', 'payment_modes.create'))
        <a href="{{ route('payment-modes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Add Payment Mode
        </a>
        @endif
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Ledger Account</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paymentModes as $paymentMode)
                    <tr>
                        <td>
                            <strong>{{ $paymentMode->name }}</strong>
                        </td>
                        <td>
                            {{ $paymentMode->ledger->name ?? 'N/A' }}
                            @if($paymentMode->ledger)
                            <br><small class="text-muted">{{ $paymentMode->ledger->code ?? '' }}</small>
                            @endif
                        </td>
                        <td>{{ $paymentMode->description ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $paymentMode->status ? 'success' : 'danger' }}">
                                {{ $paymentMode->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>{{ $paymentMode->createdBy->name ?? 'N/A' }}</td>
                        <td>{{ $paymentMode->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                @if ($permissions->contains('name', 'payment_modes.edit'))
                                <a href="{{ route('payment-modes.edit', $paymentMode) }}" 
                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                
                                @if ($permissions->contains('name', 'payment_modes.delete'))
                                <form action="{{ route('payment-modes.destroy', $paymentMode) }}" method="POST" 
                                      class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-credit-card fa-3x mb-3"></i>
                                <p>No payment modes found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        
        if (confirm('Are you sure you want to delete this payment mode?')) {
            form.submit();
        }
    });
});
</script>
@endsection

{{-- resources/views/master/payment_modes/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Create Payment Mode')

@section('content')
<div class="page-header">
    <h1 class="page-title">Create Payment Mode</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('payment-modes.index') }}">Payment Modes</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Payment Mode Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('payment-modes.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ledger Account <span class="text-danger">*</span></label>
                            <select name="ledger_id" class="form-select @error('ledger_id') is-invalid @enderror" required>
                                <option value="">Select Ledger Account</option>
                                @foreach($ledgers as $ledger)
                                <option value="{{ $ledger->id }}" {{ old('ledger_id') == $ledger->id ? 'selected' : '' }}>
                                    {{ $ledger->name }} ({{ $ledger->code ?? 'No Code' }})
                                </option>
                                @endforeach
                            </select>
                            @error('ledger_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Only Bank/Cash accounts (Type 1) are shown</small>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" 
                                      placeholder="Payment mode description...">{{ old('description') }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input type="checkbox" name="status" class="form-check-input" value="1" 
                                       {{ old('status', true) ? 'checked' : '' }}>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-start mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Payment Mode
                        </button>
                        <a href="{{ route('payment-modes.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Information</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Payment modes are linked to ledger accounts for proper accounting integration.
                </div>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i>Cash payments</li>
                    <li><i class="fas fa-check text-success me-2"></i>Bank transfers</li>
                    <li><i class="fas fa-check text-success me-2"></i>Credit card payments</li>
                    <li><i class="fas fa-check text-success me-2"></i>Online payments</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- resources/views/master/payment_modes/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Payment Mode')

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Payment Mode</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('payment-modes.index') }}">Payment Modes</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Payment Mode Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('payment-modes.update', $paymentMode) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $paymentMode->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ledger Account <span class="text-danger">*</span></label>
                            <select name="ledger_id" class="form-select @error('ledger_id') is-invalid @enderror" required>
                                <option value="">Select Ledger Account</option>
                                @foreach($ledgers as $ledger)
                                <option value="{{ $ledger->id }}" 
                                        {{ old('ledger_id', $paymentMode->ledger_id) == $ledger->id ? 'selected' : '' }}>
                                    {{ $ledger->name }} ({{ $ledger->code ?? 'No Code' }})
                                </option>
                                @endforeach
                            </select>
                            @error('ledger_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Only Bank/Cash accounts (Type 1) are shown</small>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" 
                                      placeholder="Payment mode description...">{{ old('description', $paymentMode->description) }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input type="checkbox" name="status" class="form-check-input" value="1" 
                                       {{ old('status', $paymentMode->status) ? 'checked' : '' }}>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-start mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Payment Mode
                        </button>
                        <a href="{{ route('payment-modes.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Usage Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-primary">{{ $paymentMode->salesPayments()->count() }}</h4>
                            <small class="text-muted">Sales Payments</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-success">{{ $paymentMode->purchasePayments()->count() }}</h4>
                            <small class="text-muted">Purchase Payments</small>
                        </div>
                    </div>
                </div>
                
                @if($paymentMode->salesPayments()->count() > 0 || $paymentMode->purchasePayments()->count() > 0)
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This payment mode is being used in payments. Changes should be made carefully.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection