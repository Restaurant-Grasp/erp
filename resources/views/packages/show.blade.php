@extends('layouts.app')
@section('title', 'Package Details')
@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Package Details: {{ $package->name }}</h5>
                <div>
                    @php
                    $role = auth()->user()->getRoleNames()->first();
                    $permissions = getCurrentRolePermissions($role);
                    @endphp
                    @if ($permissions->contains('name', 'package.edit'))
                    <a href="{{ route('packages.edit', $package) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    @endif
                    <a href="{{ route('packages.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Package Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Package Information</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td style="width: 40%;"><strong>Name:</strong></td>
                                <td>{{ $package->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Code:</strong></td>
                                <td>
                                    @if($package->code)
                                        <span class="badge bg-secondary">{{ $package->code }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge {{ $package->status ? 'bg-success' : 'bg-danger' }}">
                                        {{ $package->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Validity:</strong></td>
                                <td>
                                    @if($package->validity_days)
                                        <span class="badge bg-warning text-dark">{{ $package->validity_days }} days</span>
                                    @else
                                        <span class="badge bg-info">Unlimited</span>
                                    @endif
                                </td>
                            </tr>
                            @if($package->created_at)
                            <tr>
                                <td><strong>Created:</strong></td>
                                <td>{{ $package->created_at->format('M d, Y \a\t g:i A') }}</td>
                            </tr>
                            @endif
                            @if($package->updated_at && $package->updated_at != $package->created_at)
                            <tr>
                                <td><strong>Last Updated:</strong></td>
                                <td>{{ $package->updated_at->format('M d, Y \a\t g:i A') }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Pricing Information</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td style="width: 40%;"><strong>Package Price:</strong></td>
                                <td class="text-success fs-5 fw-bold">RM {{ number_format($package->package_price, 2) }}</td>
                            </tr>
                            @if($package->subtotal)
                            <tr>
                                <td><strong>Subtotal:</strong></td>
                                <td>RM {{ number_format($package->subtotal, 2) }}</td>
                            </tr>
                            @endif
                            @if($package->discount_percentage > 0)
                            <tr>
                                <td><strong>Package Discount:</strong></td>
                                <td>
                                    <span class="text-danger">{{ $package->discount_percentage }}%</span>
                                    @if($package->discount_amount)
                                        <br><small class="text-muted">(RM {{ number_format($package->discount_amount, 2) }} saved)</small>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Items Count:</strong></td>
                                <td>
                                    @php
                                        $totalItems = $package->packageItems->count();
                                    @endphp
                                    <span class="badge bg-primary">{{ $totalItems }} Item{{ $totalItems != 1 ? 's' : '' }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($package->description)
                <div class="mb-4">
                    <h6 class="text-muted">Description</h6>
                    <div class="alert alert-light">
                        {{ $package->description }}
                    </div>
                </div>
                @endif

                <!-- Package Items -->
                @if($package->packageItems->count() > 0)
                <!-- Items Section -->
                <div class="mb-4">
                    <h6 class="text-muted">Included Items ({{ $package->packageItems->count() }})</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Code</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($package->packageItems as $packageItem)
                                <tr>
                                    <td>
                                        <strong>{{ $packageItem->service->name ?? 'N/A' }}</strong>
                                        @if($packageItem->service && $packageItem->service->description)
                                            <br><small class="text-muted">{{ Str::limit($packageItem->service->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($packageItem->service && $packageItem->service->code)
                                            <span class="badge bg-secondary">{{ $packageItem->service->code }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $packageItem->quantity }}</td>
                                    <td>RM {{ number_format($packageItem->amount, 2) }}</td>
                                    <td class="fw-bold">RM {{ number_format($packageItem->amount * $packageItem->quantity, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Items Total:</th>
                                    <th>RM {{ number_format($package->packageItems->sum(function($item) { return $item->amount * $item->quantity; }), 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                @else
                <div class="alert alert-warning text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h6>No Items in Package</h6>
                    <p class="mb-0">This package doesn't contain any items yet.</p>
                </div>
                @endif

                <!-- Package Summary -->
                @if($package->packageItems->count() > 0)
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">Package Summary</h6>
                        <div class="row">
                            <div class="col-md-8">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td><strong>Subtotal:</strong></td>
                                        <td class="text-end"><strong>RM {{ number_format($package->subtotal ?? $package->packageItems->sum(function($item) { return $item->amount * $item->quantity; }), 2) }}</strong></td>
                                    </tr>
                                    @if($package->discount_percentage > 0)
                                    <tr class="text-danger">
                                        <td>Package Discount ({{ $package->discount_percentage }}%):</td>
                                        <td class="text-end">-RM {{ number_format($package->discount_amount ?? 0, 2) }}</td>
                                    </tr>
                                    @endif
                                    <tr class="table-success">
                                        <td><strong>Final Package Price:</strong></td>
                                        <td class="text-end"><strong class="text-success fs-5">RM {{ number_format($package->package_price, 2) }}</strong></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-4">
                                @php
                                    $totalValue = $package->subtotal ?? $package->packageItems->sum(function($item) { return $item->amount * $item->quantity; });
                                    $savings = $totalValue - $package->package_price;
                                    $savingsPercentage = $totalValue > 0 ? ($savings / $totalValue) * 100 : 0;
                                @endphp
                                @if($savings > 0)
                                <div class="alert alert-success mb-0">
                                    <h6 class="alert-heading"><i class="fas fa-piggy-bank me-2"></i>Customer Savings</h6>
                                    <div class="fs-4 fw-bold">RM {{ number_format($savings, 2) }}</div>
                                    <small>({{ number_format($savingsPercentage, 1) }}% discount)</small>
                                </div>
                                @elseif($savings < 0)
                                <div class="alert alert-warning mb-0">
                                    <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Premium Pricing</h6>
                                    <div class="fs-6">RM {{ number_format(abs($savings), 2) }} above item total</div>
                                </div>
                                @else
                                <div class="alert alert-info mb-0">
                                    <h6 class="alert-heading"><i class="fas fa-equals me-2"></i>Equal Pricing</h6>
                                    <div class="fs-6">No discount or premium</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Package Summary Card -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Quick Info</h6>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <div class="display-6 text-success">RM {{ number_format($package->package_price, 2) }}</div>
                    <small class="text-muted">Package Price</small>
                </div>

                <hr>

                <div class="row text-center">
                    <div class="col-6">
                        <div class="fs-4 fw-bold text-primary">{{ $package->packageItems->count() }}</div>
                        <small class="text-muted">Total Items</small>
                    </div>
                    <div class="col-6">
                        <div class="fs-4 fw-bold {{ $package->status ? 'text-success' : 'text-danger' }}">
                            {{ $package->status ? 'Active' : 'Inactive' }}
                        </div>
                        <small class="text-muted">Status</small>
                    </div>
                </div>

                <hr>

                <div class="d-grid gap-2">
                    <div class="d-flex justify-content-between">
                        <span>Validity:</span>
                        <span>
                            @if($package->validity_days)
                                {{ $package->validity_days }} days
                            @else
                                Unlimited
                            @endif
                        </span>
                    </div>
                    
                    @if($package->created_at)
                    <div class="d-flex justify-content-between">
                        <span>Created:</span>
                        <span>{{ $package->created_at->format('M d, Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        @if ($permissions->contains('name', 'package.edit') || $permissions->contains('name', 'package.delete'))
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                @if ($permissions->contains('name', 'package.edit'))
                <a href="{{ route('packages.edit', $package) }}" class="btn btn-primary btn-sm w-100 mb-2">
                    <i class="fas fa-edit me-2"></i>Edit Package
                </a>
                @endif

                @if ($permissions->contains('name', 'package.delete'))
                <form method="POST" action="{{ route('packages.destroy', $package) }}" class="w-100">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm w-100" 
                            onclick="return confirm('Are you sure you want to delete this package?\n\nPackage: {{ $package->name }}\nThis action cannot be undone and will remove all associated data.')">
                        <i class="fas fa-trash me-2"></i>Delete Package
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endif

        <!-- Usage Stats (Placeholder for future enhancement) -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Package Stats</h6>
            </div>
            <div class="card-body">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Usage statistics will be available when this package is used in sales.
                </small>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.table th {
    border-top: none;
}

.alert .display-6 {
    margin-bottom: 0;
}

.card-body .fs-4 {
    margin-bottom: 0;
}
</style>
@endpush
@endsection