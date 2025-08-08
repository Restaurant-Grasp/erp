@extends('layouts.app')

@section('title', 'Convert Lead to Customer')

@section('content')
<div class="page-header">
    <h1 class="page-title">Convert Lead to Customer</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
            <li class="breadcrumb-item"><a href="{{ route('leads.show', $lead) }}">{{ $lead->lead_no }}</a></li>
            <li class="breadcrumb-item active">Convert</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <form action="{{ route('leads.process-conversion', $lead) }}" method="POST" id="conversionForm">
            @csrf
            
            {{-- Lead Information Review --}}
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Lead Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Lead No:</dt>
                                <dd class="col-sm-7">{{ $lead->lead_no }}</dd>

                                <dt class="col-sm-5">Temple Name:</dt>
                                <dd class="col-sm-7"><strong>{{ $lead->company_name ?: $lead->contact_person }}</strong></dd>

                                <dt class="col-sm-5">Contact Person:</dt>
                                <dd class="col-sm-7">{{ $lead->contact_person }}</dd>

                                <dt class="col-sm-5">Email:</dt>
                                <dd class="col-sm-7">{{ $lead->email ?: '-' }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Phone:</dt>
                                <dd class="col-sm-7">{{ $lead->phone ?: '-' }}</dd>

                                <dt class="col-sm-5">Mobile:</dt>
                                <dd class="col-sm-7">{{ $lead->mobile ?: '-' }}</dd>

                                <dt class="col-sm-5">City:</dt>
                                <dd class="col-sm-7">{{ $lead->city ?: '-' }}</dd>

                                <dt class="col-sm-5">Status:</dt>
                                <dd class="col-sm-7">
                                    <span class="badge bg-{{ $lead->status_badge }}">
                                        {{ ucfirst(str_replace('_', ' ', $lead->lead_status)) }}
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Customer Settings --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Customer Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Credit Limit (₹)</label>
                                <input type="number" name="credit_limit" class="form-control" 
                                       value="{{ old('credit_limit', 0) }}" min="0" step="0.01">
                                <div class="form-text">Set 0 for no credit limit</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Credit Days</label>
                                <input type="number" name="credit_days" class="form-control" 
                                       value="{{ old('credit_days', 30) }}" min="0">
                                <div class="form-text">Payment terms in days</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Default Discount (%)</label>
                                <input type="number" name="discount_percentage" class="form-control" 
                                       value="{{ old('discount_percentage', 0) }}" min="0" max="100" step="0.01">
                                <div class="form-text">Applied to all transactions</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Accounting Setup --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Accounting Setup</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Automatic Ledger Creation</strong><br>
                        A ledger account will be automatically created for this customer under:<br>
                        <strong>{{ $tradeDebtorGroup->name }} ({{ $tradeDebtorGroup->code }})</strong>
                    </div>
                    
                    <p class="mb-0">
                        The ledger will be named: <strong>{{ $lead->company_name ?: $lead->contact_person }} (Customer Code)</strong>
                    </p>
                </div>
            </div>

            {{-- Conversion Summary --}}
            <div class="card mt-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Conversion Summary</h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">Please review the following before converting:</p>
                    
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i> Lead will be marked as <strong>Won</strong></li>
                        <li><i class="fas fa-check text-success me-2"></i> New customer record will be created</li>
                        <li><i class="fas fa-check text-success me-2"></i> Customer ledger will be created under Trade Debtors</li>
                        <li><i class="fas fa-check text-success me-2"></i> All lead information will be transferred</li>
                        <li><i class="fas fa-check text-success me-2"></i> Lead history will be preserved</li>
                    </ul>

                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Note:</strong> This action cannot be undone. Once converted, the lead cannot be edited.
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('leads.show', $lead) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Back to Lead
                        </a>
                        <button type="submit" class="btn btn-success btn-lg" id="convertBtn">
                            <i class="fas fa-exchange-alt me-2"></i> Convert to Customer
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="col-md-4">
        {{-- Lead Activities Summary --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Lead Summary</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-7">Total Activities:</dt>
                    <dd class="col-sm-5">{{ $lead->activities->count() }}</dd>

                    <dt class="col-sm-7">Follow-ups Done:</dt>
                    <dd class="col-sm-5">{{ $lead->follow_up_count }}</dd>

                    <dt class="col-sm-7">Quotations Sent:</dt>
                    <dd class="col-sm-5">{{ $lead->quotation_count }}</dd>

                    <dt class="col-sm-7">Total Quoted:</dt>
                    <dd class="col-sm-5">₹{{ number_format($lead->total_quoted_value, 2) }}</dd>

                    <dt class="col-sm-7">Days in Pipeline:</dt>
                    <dd class="col-sm-5">{{ $lead->created_at->diffInDays(now()) }}</dd>

                    <dt class="col-sm-7">Created On:</dt>
                    <dd class="col-sm-5">{{ $lead->created_at->format('d/m/Y') }}</dd>
                </dl>
            </div>
        </div>

        {{-- Conversion Tips --}}
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Conversion Tips</h5>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li class="mb-2">Ensure all lead information is up-to-date before converting</li>
                    <li class="mb-2">Set appropriate credit limits based on customer profile</li>
                    <li class="mb-2">Consider the customer's payment history if available</li>
                    <li class="mb-2">You can always update customer details after conversion</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#conversionForm').on('submit', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to convert this lead to a customer? This action cannot be undone.')) {
            // Disable the button to prevent double submission
            $('#convertBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Converting...');
            this.submit();
        }
    });
});
</script>
@endsection