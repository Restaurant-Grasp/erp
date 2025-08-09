@extends('layouts.app')

@section('title', 'Vendor Details')

@section('content')
<div class="page-header">
    <h1 class="page-title">Vendor Details</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('vendors.index') }}">Vendors</a></li>
            <li class="breadcrumb-item active">{{ $vendor->vendor_code }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-4">
        {{-- Vendor Info Card --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Vendor Information</h5>
                <span class="badge bg-{{ $vendor->status_badge }}">{{ ucfirst($vendor->status) }}</span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Vendor Code:</dt>
                    <dd class="col-sm-7">{{ $vendor->vendor_code }}</dd>

                    <dt class="col-sm-5">Company Name:</dt>
                    <dd class="col-sm-7"><strong>{{ $vendor->company_name }}</strong></dd>

                    <dt class="col-sm-5">Contact Person:</dt>
                    <dd class="col-sm-7">{{ $vendor->contact_person ?: '-' }}</dd>

                    <dt class="col-sm-5">Email:</dt>
                    <dd class="col-sm-7">
                        @if($vendor->email)
                        <a href="mailto:{{ $vendor->email }}">{{ $vendor->email }}</a>
                        @else
                        -
                        @endif
                    </dd>

                    <dt class="col-sm-5">Phone:</dt>
                    <dd class="col-sm-7">{{ $vendor->phone ?: '-' }}</dd>

                    <dt class="col-sm-5">Mobile:</dt>
                    <dd class="col-sm-7">{{ $vendor->mobile ?: '-' }}</dd>

                    <dt class="col-sm-5">Fax:</dt>
                    <dd class="col-sm-7">{{ $vendor->fax ?: '-' }}</dd>

                    <dt class="col-sm-5">Website:</dt>
                    <dd class="col-sm-7">
                        @if($vendor->website)
                        <a href="{{ $vendor->website }}" target="_blank">{{ $vendor->website }}</a>
                        @else
                        -
                        @endif
                    </dd>

                   
                </dl>
            </div>
        </div>

        {{-- Address Card --}}
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Address</h5>
            </div>
            <div class="card-body">
                @if($vendor->full_address)
                <address class="mb-0">
                    {{ $vendor->address_line1 }}<br>
                    @if($vendor->address_line2)
                    {{ $vendor->address_line2 }}<br>
                    @endif
                    {{ $vendor->city }}@if($vendor->state), {{ $vendor->state }}@endif @if($vendor->postcode) - {{ $vendor->postcode }}@endif<br>
                    {{ $vendor->country }}
                </address>
                @else
                <p class="text-muted mb-0">No address provided</p>
                @endif

                @if($vendor->registration_no || $vendor->tax_no)
                <hr>
                <dl class="row mb-0">
                    @if($vendor->registration_no)
                    <dt class="col-sm-5">Registration No:</dt>
                    <dd class="col-sm-7">{{ $vendor->registration_no }}</dd>
                    @endif

                    @if($vendor->tax_no)
                    <dt class="col-sm-5">GST No:</dt>
                    <dd class="col-sm-7">{{ $vendor->tax_no }}</dd>
                    @endif
                </dl>
                @endif
            </div>
        </div>

        {{-- Business Terms Card --}}
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Business Terms</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Payment Terms:</dt>
                    <dd class="col-sm-7">{{ $vendor->payment_terms }} days</dd>

                    <dt class="col-sm-5">Credit Limit:</dt>
                    <dd class="col-sm-7">
                        @if($vendor->credit_limit > 0)
                        ₹{{ number_format($vendor->credit_limit, 2) }}
                        @else
                        <span class="text-muted">No limit</span>
                        @endif
                    </dd>

                    <dt class="col-sm-5">Ledger Account:</dt>
                    <dd class="col-sm-7">
                        @if($vendor->ledger)
                        {{ $vendor->ledger->name }}
                        @else
                        <span class="text-muted">Not created</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>

        {{-- Banking Information --}}
        @if($vendor->bank_name || $vendor->bank_account_no)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Banking Information</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    @if($vendor->bank_name)
                    <dt class="col-sm-5">Bank Name:</dt>
                    <dd class="col-sm-7">{{ $vendor->bank_name }}</dd>
                    @endif

                    @if($vendor->bank_account_no)
                    <dt class="col-sm-5">Account No:</dt>
                    <dd class="col-sm-7">{{ $vendor->bank_account_no }}</dd>
                    @endif
                </dl>
            </div>
        </div>
        @endif

        {{-- Service Types Card --}}
        @if($vendor->serviceTypes->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Service Types</h5>
            </div>
            <div class="card-body">
                @foreach($vendor->serviceTypes as $serviceType)
                <span class="badge bg-info me-1 mb-1">{{ $serviceType->name }}</span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Actions --}}
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @can('vendors.edit')
                    <a href="{{ route('vendors.edit', $vendor) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i> Edit Vendor
                    </a>
                    @endcan

                    @if (Route::has('purchase-orders.create'))
                    @can('purchase-orders.create')
                    <a href="{{ route('purchase-orders.create', ['vendor_id' => $vendor->id]) }}" class="btn btn-outline-primary">
                        <i class="fas fa-shopping-cart me-2"></i> Create Purchase Order
                    </a>
                    @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        {{-- Statistics Cards --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-shopping-cart fa-2x text-primary mb-2"></i>
                        <h6 class="text-muted">Total Purchase Orders</h6>
                        <h3>{{ $statistics['total_purchases'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-rupee-sign fa-2x text-success mb-2"></i>
                        <h6 class="text-muted">Total Amount</h6>
                        <h3>₹{{ number_format($statistics['total_amount'], 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-exclamation-circle fa-2x text-danger mb-2"></i>
                        <h6 class="text-muted">Outstanding</h6>
                        <h3>₹{{ number_format($statistics['outstanding_amount'], 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Products Card --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Products from this Vendor</h5>
                @if (Route::has('product-vendors.index'))
                <a href="{{ route('product-vendors.index', ['vendor_id' => $vendor->id]) }}" class="btn btn-sm btn-outline-primary">
                    Manage Products
                </a>
                @endif
            </div>
            <div class="card-body">
                @if($vendor->products->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Product Code</th>
                                <th>Product Name</th>
                                <th>Vendor Product Code</th>
                                <th>Vendor Price</th>
                                <th>Lead Time</th>
                                <th>Preferred</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendor->products as $product)
                            <tr>
                                <td>
                                    @if (Route::has('products.show'))
                                    <a href="{{ route('products.show', $product) }}">
                                        {{ $product->product_code }}
                                    </a>
                                    @else
                                    {{ $product->product_code }}
                                    @endif
                                </td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->pivot->vendor_product_code ?: '-' }}</td>
                                <td>
                                    @if($product->pivot->vendor_price > 0)
                                    ₹{{ number_format($product->pivot->vendor_price, 2) }}
                                    @else
                                    -
                                    @endif
                                </td>
                                <td>{{ $product->pivot->lead_time_days }} days</td>
                                <td>
                                    @if($product->pivot->is_preferred)
                                    <span class="badge bg-success">Yes</span>
                                    @else
                                    <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center py-3 mb-0">No products assigned to this vendor</p>
                @endif
            </div>
        </div>

        @if (Route::has('purchase-orders.index'))
        {{-- Recent Purchase Orders --}}
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Purchase Orders</h5>
                <a href="{{ route('purchase-orders.index', ['vendor_id' => $vendor->id]) }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                {{-- This will be populated when purchase order module is implemented --}}
                <p class="text-muted text-center py-3 mb-0">No purchase orders found</p>
            </div>
        </div>
        @endif

        {{-- Notes --}}
        @if($vendor->notes)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Notes</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $vendor->notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection