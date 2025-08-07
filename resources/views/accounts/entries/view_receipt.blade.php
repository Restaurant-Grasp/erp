@extends('layouts.app')

@section('content')

@push('styles')
<style>
    :root {
    --primary-green: #28a745;
    --dark-green: #218838;
}
.section-header{
           background: #00a551;
         color:white;
}
    /* Modern View Receipt Styling */
    .view-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
        margin-bottom: 24px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .view-card:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
    }

    .view-card-header {

        padding: 20px 24px;
        border-bottom: none;
        font-weight: 600;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: between;
    }

    .view-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        flex: 1;
    }

    .view-card-actions {
        display: flex;
        gap: 8px;
    }

    .view-card-body {
        padding: 24px;
    }

    /* Status Badges */
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .status-cash {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }

    .status-cheque {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        color: #000;
    }

    .status-online {
        background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
        color: white;
    }

    /* Info Display Cards */
    .info-card {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        transition: all 0.3s ease;
    }

    .info-card:hover {
        background: #f1f3f4;
        transform: translateY(-2px);
    }

    .info-label {
        font-size: 12px;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .info-value {
        font-size: 16px;
        font-weight: 600;
        color: #2d3748;
    }

    .info-value.large {
        font-size: 24px;
        color: var(--primary-green);
    }

    .info-value.highlight {
        color: var(--primary-green);
        font-weight: 700;
    }

    /* Special Cards */
    .payment-details-card {
        border-left: 4px solid #ffc107;
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    }

    .returned-cheque-card {
        border-left: 4px solid #dc3545;
        background: linear-gradient(135deg, #f8d7da 0%, #f1b0b7 100%);
    }

    .details-table-card {
        border-left: 4px solid var(--primary-green);
    }

    .narration-card {
        border-left: 4px solid #17a2b8;
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    }

    /* Table Styling */
    .modern-table {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .modern-table table {
        margin: 0;
        border: none;
    }

    .modern-table thead th {
        background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
        color: white;
        border: none;
        padding: 16px 12px;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .modern-table tbody td {
        padding: 12px;
        vertical-align: middle;
        border-top: 1px solid #e9ecef;
        border-left: none;
        border-right: none;
        border-bottom: none;
    }

    .modern-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .modern-table tfoot th {
        background: var(--light-green);
        color: var(--dark-green);
        font-weight: 700;
        padding: 16px 12px;
        border: none;
    }

    /* Action Buttons */
    /* .btn-modern {
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
    } */


.btn-primary-modern {
    background-color: #007bff;
    color: white;
    border: none;
}

.btn-primary-modern:hover,
.btn-primary-modern:focus {
    background-color: #0056b3;
    color: white;
}

.btn-success-modern {
    background-color: var(--primary-green); /* Define in :root */
    color: white;
    border: none;
}

.btn-success-modern:hover,
.btn-success-modern:focus {
    background-color: var(--dark-green); /* Define in :root */
    color: white;
}

.btn-secondary-modern {
    background-color: #6c757d;
    color: white;
    border: none;
}

.btn-secondary-modern:hover,
.btn-secondary-modern:focus {
    background-color: #545b62;
    color: white;
}

.btn-warning-modern {
    background-color: #ffc107;
    color: #000;
    border: none;
}

.btn-warning-modern:hover,
.btn-warning-modern:focus {
    background-color: #e0a800;
    color: #000;
}


    /* Discount row styling */
    .discount-row {
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
    }

    .discount-row td {
        color: #856404;
        font-weight: 600;
    }

    .discount-amount {
        color: #dc3545 !important;
        font-weight: 700;
    }

    /* Metadata section */
    .metadata-section {
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
        padding: 16px 24px;
        font-size: 12px;
        color: #6c757d;
        border-radius: 0 0 12px 12px;
        margin: -24px -24px 0 -24px;
        margin-top: 24px;
    }

    /* View badge */
    .view-badge {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-left: 10px;
    }

    /* Amount display */
    .amount-highlight {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        text-align: center;
        font-size: 18px;
        font-weight: 700;
        margin: 8px 0;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .view-card-body {
            padding: 16px;
        }
        
        .view-card-header {
            flex-direction: column;
            gap: 12px;
            text-align: center;
        }
        
        .view-card-actions {
            order: -1;
        }
        
        .btn-modern {
            font-size: 12px;
            padding: 6px 12px;
        }
        
        .info-value.large {
            font-size: 20px;
        }
    }

    /* Animation */
    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

<div class="page-header">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home me-2"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('accounts.receipt.list') }}">Receipt List</a></li>
            <li class="breadcrumb-item active" aria-current="page">View Receipt</li>
        </ol>
    </nav>
</div>

<div class="card fade-in">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="view-card-title mb-0">
       
            Receipt Voucher - {{ $entry->entry_code }}
        </h6>

        <!-- Action Buttons -->
        <div class="view-card-actions">
            @if(empty($entry->inv_type))
                <a href="{{ route('accounts.receipt.edit', $entry->id) }}" class="btn btn-modern btn-primary-modern me-2">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('accounts.receipt.copy', $entry->id) }}" class="btn btn-modern btn-success-modern me-2">
                    <i class="fas fa-copy me-1"></i> Copy
                </a>
            @endif

            <a href="{{ route('accounts.receipt.list') }}" class="btn btn-modern btn-secondary-modern">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card-body">
        <!-- Basic Information Grid -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="info-card">
                    <div class="info-label">Date</div>
                    <div class="info-value">{{ date('d-m-Y', strtotime($entry->date)) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-card">
                    <div class="info-label">Receipt Mode</div>
                    <div class="info-value">
                        <span class="status-badge status-{{ strtolower($entry->payment) }}">
                            {{ $entry->payment }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-card">
                    <div class="info-label">Fund</div>
                    <div class="info-value">{{ $entry->fund->name }}{{ $entry->fund->code ? ' (' . $entry->fund->code . ')' : '' }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-card">
                    <div class="info-label">Total Amount</div>
                    @php
                    $dr_total = $entry->dr_total;
                    if($discountItem && !empty($discountItem->amount)) $dr_total -= $discountItem->amount;
                    @endphp
                    <div class="info-value large">RM {{ number_format($dr_total, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Receipt Details -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="info-card">
                    <div class="info-label">Received From</div>
                    <div class="info-value highlight">{{ $entry->paid_to }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-card">
                    <div class="info-label">Bank/Cash Account</div>
                    <div class="info-value">
                        {{ $debitAccount->ledger->name }}
                        <br><small class="text-muted">({{ $debitAccount->ledger->left_code }}/{{ $debitAccount->ledger->right_code }})</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Specific Details -->
        @if($entry->payment == 'CHEQUE' && $entry->cheque_no)
        <div class="view-card payment-details-card fade-in">
            <div class="view-card-body">
                <h6 class="mb-3">
                    <i class="fas fa-money-check text-warning"></i>
                    Cheque Details
                </h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-label">Cheque Number</div>
                        <div class="info-value">{{ $entry->cheque_no }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Cheque Date</div>
                        <div class="info-value">{{ date('d-m-Y', strtotime($entry->cheque_date)) }}</div>
                    </div>
                    @if($entry->collection_date)
                    <div class="col-md-4">
                        <div class="info-label">Collection Date</div>
                        <div class="info-value">{{ date('d-m-Y', strtotime($entry->collection_date)) }}</div>
                    </div>
                    @endif
                </div>
                
                @if($entry->return_date)
                <div class="alert alert-danger mt-3 mb-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>
                            <strong>Cheque Returned:</strong> {{ date('d-m-Y', strtotime($entry->return_date)) }}
                            @if($entry->extra_charge > 0)
                                <br><strong>Bank Charges:</strong> RM {{ number_format($entry->extra_charge, 2) }}
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if($entry->payment == 'ONLINE' && $entry->cheque_no)
        <div class="view-card payment-details-card fade-in">
            <div class="view-card-body">
                <h6 class="mb-3">
                    <i class="fas fa-credit-card text-info"></i>
                    Online Transaction Details
                </h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-label">Transaction Number</div>
                        <div class="info-value">{{ $entry->cheque_no }}</div>
                    </div>
                    @if($entry->cheque_date)
                    <div class="col-md-6">
                        <div class="info-label">Transaction Date</div>
                        <div class="info-value">{{ date('d-m-Y', strtotime($entry->cheque_date)) }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
<br>
<!-- Receipt Items Table -->
<div class="card  fade-in">
    <div class="card-header section-header">
        <h6 class="view-card-title">
            Receipt Details
        </h6>
    </div>
    <div class="card-body px-4">
        <div class="table">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>Details</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @if($discountItem)
                    <tr class="discount-row">
                        <td>
                            <strong>[Discount]</strong> {{ $discountItem->ledger->name }}
                            <br><small class="text-muted">({{ $discountItem->ledger->left_code }}/{{ $discountItem->ledger->right_code }})</small>
                        </td>
                        <td>{{ $discountItem->details ?: 'Discount Applied' }}</td>
                        <td style="text-align: right;" class="discount-amount">
                            <i class="fas fa-minus-circle"></i>
                            RM {{ number_format($discountItem->amount, 2) }}
                        </td>
                    </tr>
                    @endif
                    
                    @foreach($creditItems as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->ledger->name }}</strong>
                            <br><small class="text-muted">({{ $item->ledger->left_code }}/{{ $item->ledger->right_code }})</small>
                        </td>
                        <td>{{ $item->details ?: '-' }}</td>
                        <td style="text-align: right;">
                            RM {{ number_format($item->amount, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">
                            <i class="fas fa-calculator me-2"></i>
                            Total Amount:
                        </th>
                        <th style="text-align: right;">
                            @php
                            $cr_total = $entry->cr_total;
                            if($discountItem && !empty($discountItem->amount)) $cr_total -= $discountItem->amount;
                            @endphp
                            RM {{ number_format($cr_total, 2) }}
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<br>
<!-- Narration -->
@if($entry->narration)
<div class="card fade-in">
    <div class="card-header section-header">
        <h6 class="view-card-title">
            Receipt Particulars
        </h6>
    </div>
    <div class="card-body">
        <p class="mb-0" style="line-height: 1.6; font-size: 15px;">{{ $entry->narration }}</p>
    </div>
</div>
@endif



@endsection