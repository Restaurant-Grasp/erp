@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')
<div class="page-header">
    <h1 class="page-title">Customer Details</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
            <li class="breadcrumb-item active">{{ $customer->customer_code }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-4">
        {{-- Customer Info Card --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Customer Information</h5>
                <span class="badge bg-{{ $customer->status_badge }}">{{ ucfirst($customer->status) }}</span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Customer Code:</dt>
                    <dd class="col-sm-7">{{ $customer->customer_code }}</dd>

                    <dt class="col-sm-5">Company Name:</dt>
                    <dd class="col-sm-7"><strong>{{ $customer->company_name }}</strong></dd>
                    <dt class="col-sm-5">Fax:</dt>
                    <dd class="col-sm-7">{{ $customer->fax ?: '-' }}</dd>

                    <dt class="col-sm-5">Website:</dt>
                    <dd class="col-sm-7">
                        @if($customer->website)
                        <a href="{{ $customer->website }}" target="_blank">{{ $customer->website }}</a>
                        @else
                        -
                        @endif
                    </dd>

          

                    <dt class="col-sm-5">Source:</dt>
                    <dd class="col-sm-7">{{ ucfirst($customer->source) }}</dd>

                    @if($customer->reference_by)
                    <dt class="col-sm-5">Reference By:</dt>
                    <dd class="col-sm-7">{{ $customer->reference_by }}</dd>
                    @endif

                    <dt class="col-sm-5">Assigned To:</dt>
                    <dd class="col-sm-7">{{ $customer->assignedTo->name ?? '-' }}</dd>

                    @if($customer->lead)
                    <dt class="col-sm-5">Converted From:</dt>
                    <dd class="col-sm-7">
                        <a href="{{ route('leads.show', $customer->lead) }}">
                            Lead #{{ $customer->lead->lead_no }}
                        </a>
                    </dd>
                    @endif
                </dl>
            </div>
        </div>
 {{-- Contact Details Section --}}
  @if($customer->contacts && $customer->contacts->count() > 0)
    <div class="card shadow-sm mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Contact Details</h5>
        <span class="badge bg-secondary">{{ $customer->contacts->count() }} Contact{{ $customer->contacts->count() > 1 ? 's' : '' }}</span>
      </div>
      <div class="card-body">
        <div class="row">
          @foreach($customer->contacts as $contact)
            <div class="col-md-6 mb-3">
              <div class="contact-card border rounded p-3 h-100 {{ $contact->is_primary ? 'border-success bg-light-success' : '' }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h6 class="mb-0 fw-bold text-dark">
                    {{ $contact->name }}
                    @if($contact->is_primary)
                      <span class="badge bg-success ms-1" style="font-size: 0.7em;">Primary</span>
                    @endif
                  </h6>
                  <div class="contact-types">
                    @if($contact->is_billing_contact)
                      <span class="badge bg-info me-1" style="font-size: 0.7em;">Billing</span>
                    @endif
                    @if($contact->is_technical_contact)
                      <span class="badge bg-warning me-1" style="font-size: 0.7em;">Technical</span>
                    @endif
                  </div>
                </div>
                
                <div class="contact-info">
                  @if($contact->email)
                    <div class="mb-2">
                      <i class="fas fa-envelope text-muted me-2"></i>
                      <a href="mailto:{{ $contact->email }}" class="text-decoration-none">{{ $contact->email }}</a>
                    </div>
                  @endif
                  
                  @if($contact->phone)
                    <div class="mb-2">
                      <i class="fas fa-phone text-muted me-2"></i>
                      <a href="tel:{{ $contact->phone }}" class="text-decoration-none">{{ $contact->phone }}</a>
                    </div>
                  @endif
                  
                  @if(!$contact->email && !$contact->phone)
                    <small class="text-muted">No contact information available</small>
                  @endif
                </div>
                
                @if($contact->created_at)
                  <div class="mt-2 pt-2 border-top">
                    <small class="text-muted">
                      <i class="fas fa-clock me-1"></i>
                      Added {{ $contact->created_at->diffForHumans() }}
                    </small>
                  </div>
                @endif
              </div>
            </div>
          @endforeach
        </div>
        
        {{-- Contact Summary --}}
        @if($customer->contacts->count() > 2)
          <div class="mt-3 pt-3 border-top">
            <div class="row text-center">
              @php
                $primaryCount = $customer->contacts->where('is_primary', true)->count();
                $billingCount = $customer->contacts->where('is_billing_contact', true)->count();
                $technicalCount = $customer->contacts->where('is_technical_contact', true)->count();
                $emailCount = $customer->contacts->whereNotNull('email')->count();
                $phoneCount = $customer->contacts->whereNotNull('phone')->count();
              @endphp
              
              <div class="col">
                <div class="fw-bold text-success">{{ $primaryCount }}</div>
                <small class="text-muted">Primary</small>
              </div>
              <div class="col">
                <div class="fw-bold text-info">{{ $billingCount }}</div>
                <small class="text-muted">Billing</small>
              </div>
              <div class="col">
                <div class="fw-bold text-warning">{{ $technicalCount }}</div>
                <small class="text-muted">Technical</small>
              </div>
              <div class="col">
                <div class="fw-bold text-primary">{{ $emailCount }}</div>
                <small class="text-muted">With Email</small>
              </div>
              <div class="col">
                <div class="fw-bold text-secondary">{{ $phoneCount }}</div>
                <small class="text-muted">With Phone</small>
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>
  @else
    <div class="card shadow-sm mb-4">
      <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Contact Details</h5>
      </div>
      <div class="card-body text-center py-4">
        <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
        <p class="text-muted mb-0">No contact details available for this customer.</p>
        @if(!$customer->is_converted && auth()->user()->can('leads.edit'))
          <a href="{{ route('leads.edit', $lead) }}" class="btn btn-outline-primary btn-sm mt-2">
            <i class="fas fa-plus me-1"></i> Add Contacts
          </a>
        @endif
      </div>
    </div>
  @endif
        {{-- Address Card --}}
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Address</h5>
            </div>
            <div class="card-body">
                @if($customer->full_address)
                <address class="mb-0">
                    {{ $customer->address_line1 }}<br>
                    @if($customer->address_line2)
                    {{ $customer->address_line2 }}<br>
                    @endif
                    {{ $customer->city }}@if($customer->state), {{ $customer->state }}@endif @if($customer->postcode) - {{ $customer->postcode }}@endif<br>
                    {{ $customer->country }}
                </address>
                @else
                <p class="text-muted mb-0">No address provided</p>
                @endif

                @if($customer->registration_no || $customer->tax_no)
                <hr>
                <dl class="row mb-0">
                    @if($customer->registration_no)
                    <dt class="col-sm-5">Registration No:</dt>
                    <dd class="col-sm-7">{{ $customer->registration_no }}</dd>
                    @endif

                    @if($customer->tax_no)
                    <dt class="col-sm-5">GST No:</dt>
                    <dd class="col-sm-7">{{ $customer->tax_no }}</dd>
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
                    <dt class="col-sm-5">Credit Limit:</dt>
                    <dd class="col-sm-7">
                        @if($customer->credit_limit > 0)
                        ₹{{ number_format($customer->credit_limit, 2) }}
                        @else
                        <span class="text-muted">No limit</span>
                        @endif
                    </dd>

                    <dt class="col-sm-5">Credit Days:</dt>
                    <dd class="col-sm-7">{{ $customer->credit_days }} days</dd>

                    <dt class="col-sm-5">Discount:</dt>
                    <dd class="col-sm-7">{{ $customer->discount_percentage }}%</dd>

                    <dt class="col-sm-5">Ledger Account:</dt>
                    <dd class="col-sm-7">
                        @if($customer->ledger)
                        {{ $customer->ledger->name }}
                        @else
                        <span class="text-muted">Not created</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>

        {{-- Service Types Card --}}
        @if($customer->serviceTypes->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Service Types</h5>
            </div>
            <div class="card-body">
                @foreach($customer->serviceTypes as $serviceType)
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
                    @can('customers.edit')
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i> Edit Customer
                    </a>
                    @endcan

                    <!-- @can('quotations.create')
                     @if (Route::has('quotations.create'))
                    <a href="{{ route('quotations.create', ['customer_id' => $customer->id]) }}" class="btn btn-outline-primary">
                        <i class="fas fa-file-alt me-2"></i> Create Quotation
                    </a>
                    @endif
                    @endcan -->
                    @if (Route::has('invoices.create'))
                    @can('invoices.create')
                    <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-outline-primary">
                        <i class="fas fa-file-invoice me-2"></i> Create Invoice
                    </a>
                    @endcan
                    @endif
                    @can('customers.statement')
                    <a href="{{ route('customers.statement', $customer) }}" class="btn btn-outline-info">
                        <i class="fas fa-file-pdf me-2"></i> View Statement
                    </a>
                    @endcan
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
                        <i class="fas fa-file-invoice fa-2x text-primary mb-2"></i>
                        <h6 class="text-muted">Total Invoices</h6>
                        <h3>{{ $statistics['total_invoices'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-rupee-sign fa-2x text-success mb-2"></i>
                        <h6 class="text-muted">Total Revenue</h6>
                        <h3>₹{{ number_format($statistics['total_revenue'], 2) }}</h3>
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
        @if (Route::has('quotations.index'))
        {{-- Recent Quotations --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Quotations</h5>
                <a href="{{ route('quotations.index', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                @if($customer->quotations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Quotation No</th>
                                <th>Date</th>
                                <th>Valid Until</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customer->quotations as $quotation)
                            <tr>
                                <td>
                                    <a href="{{ route('quotations.show', $quotation) }}">
                                        {{ $quotation->quotation_no }}
                                    </a>
                                </td>
                                <td>{{ $quotation->quotation_date->format('d/m/Y') }}</td>
                                <td>{{ $quotation->valid_until ? $quotation->valid_until->format('d/m/Y') : '-' }}</td>
                                <td>₹{{ number_format($quotation->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $quotation->status_badge }}">
                                        {{ ucfirst($quotation->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center py-3 mb-0">No quotations found</p>
                @endif
            </div>
        </div>
        @endif
        @if (Route::has('invoices.index'))
        {{-- Recent Invoices --}}
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Invoices</h5>
                <a href="{{ route('invoices.index', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                @if($customer->invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Invoice No</th>
                                <th>Date</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                <th>Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customer->invoices as $invoice)
                            <tr>
                                <td>
                                    <a href="{{ route('invoices.show', $invoice) }}">
                                        {{ $invoice->invoice_no }}
                                    </a>
                                </td>
                                <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                                <td>{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</td>
                                <td>₹{{ number_format($invoice->total_amount, 2) }}</td>
                                <td>
                                    @if($invoice->balance_amount > 0)
                                    <span class="text-danger">₹{{ number_format($invoice->balance_amount, 2) }}</span>
                                    @else
                                    ₹0.00
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $invoice->status_badge }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center py-3 mb-0">No invoices found</p>
                @endif
            </div>
        </div>
        @endif
        {{-- Notes --}}
        @if($customer->notes)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Notes</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $customer->notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection