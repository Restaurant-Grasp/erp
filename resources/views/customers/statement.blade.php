@extends('layouts.app')

@section('title', 'Customer Statement')

@section('content')
<div class="page-header d-print-none">
    <h1 class="page-title">Customer Statement</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customers.show', $customer) }}">{{ $customer->customer_code }}</a></li>
            <li class="breadcrumb-item active">Statement</li>
        </ol>
    </nav>
</div>

{{-- Print Header --}}
<div class="d-none d-print-block text-center mb-4">
    <h2>{{ config('app.name', 'Company Name') }}</h2>
    <h4>Customer Statement</h4>
</div>

{{-- Statement Actions --}}
<div class="card d-print-none mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <form method="GET" action="{{ route('customers.statement', $customer) }}" class="row g-2">
                    <div class="col-auto">
                        <label class="col-form-label">From Date:</label>
                    </div>
                    <div class="col-auto">
                        <input type="date" name="from_date" class="form-control" 
                               value="{{ request('from_date', now()->subMonths(3)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-auto">
                        <label class="col-form-label">To Date:</label>
                    </div>
                    <div class="col-auto">
                        <input type="date" name="to_date" class="form-control" 
                               value="{{ request('to_date', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <button onclick="window.print()" class="btn btn-outline-primary">
                    <i class="fas fa-print me-2"></i>Print
                </button>
                <button onclick="exportToPDF()" class="btn btn-outline-danger">
                    <i class="fas fa-file-pdf me-2"></i>Export PDF
                </button>
                <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Customer Information --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5>Customer Details</h5>
                <address>
                    <strong>{{ $customer->company_name }}</strong><br>
                    @if($customer->contact_person)
                        Attn: {{ $customer->contact_person }}<br>
                    @endif
                    @if($customer->address_line1)
                        {{ $customer->address_line1 }}<br>
                    @endif
                    @if($customer->address_line2)
                        {{ $customer->address_line2 }}<br>
                    @endif
                    @if($customer->city || $customer->state || $customer->postcode)
                        {{ $customer->city }}@if($customer->state), {{ $customer->state }}@endif @if($customer->postcode) - {{ $customer->postcode }}@endif<br>
                    @endif
                    @if($customer->phone)
                        Phone: {{ $customer->phone }}<br>
                    @endif
                    @if($customer->email)
                        Email: {{ $customer->email }}
                    @endif
                </address>
            </div>
            <div class="col-md-6 text-md-end">
                <h5>Statement Information</h5>
                <dl class="row">
                    <dt class="col-sm-8">Customer Code:</dt>
                    <dd class="col-sm-4">{{ $customer->customer_code }}</dd>
                    
                    <dt class="col-sm-8">Statement Date:</dt>
                    <dd class="col-sm-4">{{ now()->format('d/m/Y') }}</dd>
                    
                    <dt class="col-sm-8">Period:</dt>
                    <dd class="col-sm-4">{{ request('from_date', now()->subMonths(3)->format('d/m/Y')) }} - {{ request('to_date', now()->format('d/m/Y')) }}</dd>
                    
                    <dt class="col-sm-8">Credit Limit:</dt>
                    <dd class="col-sm-4">
                        @if($customer->credit_limit > 0)
                            ₹{{ number_format($customer->credit_limit, 2) }}
                        @else
                            No Limit
                        @endif
                    </dd>
                    
                    <dt class="col-sm-8">Credit Days:</dt>
                    <dd class="col-sm-4">{{ $customer->credit_days }} days</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

{{-- Statement Table --}}
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Transaction Details</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th width="100">Date</th>
                        <th width="120">Transaction No</th>
                        <th width="100">Type</th>
                        <th>Description</th>
                        <th width="120" class="text-end">Debit (₹)</th>
                        <th width="120" class="text-end">Credit (₹)</th>
                        <th width="120" class="text-end">Balance (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $balance = 0;
                        $totalDebit = 0;
                        $totalCredit = 0;
                        $transactions = collect();
                        
                        // Add invoices as debits
                        foreach($invoices as $invoice) {
                            $transactions->push([
                                'date' => $invoice->invoice_date,
                                'number' => $invoice->invoice_no,
                                'type' => 'Invoice',
                                'description' => 'Sales Invoice',
                                'debit' => $invoice->total_amount,
                                'credit' => 0
                            ]);
                        }
                        
                        // Add payments as credits (when payment module is ready)
                        // foreach($payments as $payment) {
                        //     $transactions->push([...]);
                        // }
                        
                        // Sort by date
                        $transactions = $transactions->sortBy('date');
                    @endphp
                    
                    {{-- Opening Balance Row --}}
                    <tr class="table-secondary">
                        <td colspan="6"><strong>Opening Balance</strong></td>
                        <td class="text-end"><strong>₹0.00</strong></td>
                    </tr>
                    
                    @forelse($transactions as $transaction)
                        @php
                            $balance += $transaction['debit'] - $transaction['credit'];
                            $totalDebit += $transaction['debit'];
                            $totalCredit += $transaction['credit'];
                        @endphp
                        <tr>
                            <td>{{ $transaction['date']->format('d/m/Y') }}</td>
                            <td>{{ $transaction['number'] }}</td>
                            <td>{{ $transaction['type'] }}</td>
                            <td>{{ $transaction['description'] }}</td>
                            <td class="text-end">
                                @if($transaction['debit'] > 0)
                                    {{ number_format($transaction['debit'], 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">
                                @if($transaction['credit'] > 0)
                                    {{ number_format($transaction['credit'], 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">
                                @if($balance >= 0)
                                    {{ number_format($balance, 2) }}
                                @else
                                    <span class="text-danger">({{ number_format(abs($balance), 2) }})</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No transactions found for the selected period
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($transactions->isNotEmpty())
                <tfoot>
                    <tr class="table-secondary">
                        <th colspan="4" class="text-end">Period Total:</th>
                        <th class="text-end">{{ number_format($totalDebit, 2) }}</th>
                        <th class="text-end">{{ number_format($totalCredit, 2) }}</th>
                        <th class="text-end">
                            @if($balance >= 0)
                                {{ number_format($balance, 2) }}
                            @else
                                <span class="text-danger">({{ number_format(abs($balance), 2) }})</span>
                            @endif
                        </th>
                    </tr>
                    <tr class="table-primary">
                        <th colspan="6" class="text-end">Closing Balance:</th>
                        <th class="text-end">
                            @if($balance >= 0)
                                ₹{{ number_format($balance, 2) }}
                            @else
                                <span class="text-danger">(₹{{ number_format(abs($balance), 2) }})</span>
                            @endif
                        </th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

{{-- Summary --}}
@if($transactions->isNotEmpty())
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Account Summary</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <dl class="row">
                    <dt class="col-sm-6">Total Invoiced:</dt>
                    <dd class="col-sm-6">₹{{ number_format($totalDebit, 2) }}</dd>
                    
                    <dt class="col-sm-6">Total Paid:</dt>
                    <dd class="col-sm-6">₹{{ number_format($totalCredit, 2) }}</dd>
                    
                    <dt class="col-sm-6">Outstanding Balance:</dt>
                    <dd class="col-sm-6">
                        @if($balance > 0)
                            <span class="text-danger">₹{{ number_format($balance, 2) }}</span>
                        @else
                            ₹0.00
                        @endif
                    </dd>
                </dl>
            </div>
            <div class="col-md-6">
                <dl class="row">
                    <dt class="col-sm-6">Credit Limit:</dt>
                    <dd class="col-sm-6">
                        @if($customer->credit_limit > 0)
                            ₹{{ number_format($customer->credit_limit, 2) }}
                        @else
                            No Limit
                        @endif
                    </dd>
                    
                    <dt class="col-sm-6">Available Credit:</dt>
                    <dd class="col-sm-6">
                        @if($customer->credit_limit > 0)
                            @php $available = $customer->credit_limit - $balance; @endphp
                            @if($available > 0)
                                <span class="text-success">₹{{ number_format($available, 2) }}</span>
                            @else
                                <span class="text-danger">₹0.00 (Overlimit)</span>
                            @endif
                        @else
                            Unlimited
                        @endif
                    </dd>
                    
                    <dt class="col-sm-6">Overdue Amount:</dt>
                    <dd class="col-sm-6">
                        @php 
                            $overdue = $invoices->where('status', 'overdue')->sum('balance_amount');
                        @endphp
                        @if($overdue > 0)
                            <span class="text-danger">₹{{ number_format($overdue, 2) }}</span>
                        @else
                            ₹0.00
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Footer for Print --}}
<div class="d-none d-print-block mt-5 pt-5 text-center">
    <p class="text-muted">
        This is a computer generated statement and does not require signature.<br>
        Generated on: {{ now()->format('d/m/Y H:i:s') }}
    </p>
</div>

<style>
@media print {
    .page-header, .breadcrumb, .d-print-none {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .table {
        font-size: 12px;
    }
}
</style>

<script>
function exportToPDF() {
    window.print();
}
</script>
@endsection