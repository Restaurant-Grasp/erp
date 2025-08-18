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

