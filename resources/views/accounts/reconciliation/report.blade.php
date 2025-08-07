@extends('layouts.app')
@section('title', 'Bank Reconciliation Report')
@section('content')

<div class="row">
    <div class="col-12">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3 no-print">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('accounts.reconciliation.index') }}">Bank Reconciliation</a></li>
                <li class="breadcrumb-item active">Reconciliation Report</li>
            </ol>
        </nav>
        
        <!-- Print Button -->
        <div class="row mb-3 no-print">
            <div class="col-12 text-end">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print me-2"></i>Print Report
                </button>
                <a href="{{ route('accounts.reconciliation.index') }}" class="btn btn-secondary ms-2">
                    <i class="fas fa-arrow-left me-1"></i>Back to List
                </a>
            </div>
        </div>
        
        <div class="card print-container">
            <div class="card-body">
                <!-- Company Header -->
                <div class="company-header">
                    <div class="logo">
                        <img src="{{ asset('public/assets/logo.jpeg') }}" alt="RSK Logo">
                    </div>
                    <div class="company-info">
                        <strong>RSK Canvas Trading</strong><br>
                        No. 8 Lot 2921, Jalan PJS 3/1, Taman Medan, 46000 Petaling Jaya,<br>
                        Selangor Darul Ehsan.<br>
                        <span>Tel: +603-7781 7434 / +603-7785 7434</span><br>
                        E-mail: sales@rsk.com.my
                    </div>
                </div>
                
                <!-- Report Header -->
                <div class="text-center mb-4 report-header">
                    <h2 class="mb-1">BANK RECONCILIATION STATEMENT</h2>
                    <h4 class="text-primary mb-1">{{ $reconciliation->ledger->name }}</h4>
                    <h5 class="text-muted">For the month of {{ $reconciliation->month_display }}</h5>
                    <div class="status-badge mt-2">
                        @php
                            $statusConfig = [
                                'draft' => ['bg-warning', 'Draft'],
                                'completed' => ['bg-success', 'Completed'],
                                'locked' => ['bg-secondary', 'Locked']
                            ];
                            $config = $statusConfig[$reconciliation->status] ?? ['bg-secondary', 'Unknown'];
                        @endphp
                        <span class="badge {{ $config[0] }} fs-6">{{ $config[1] }}</span>
                    </div>
                </div>
                
                <!-- Opening Balance -->
                <div class="balance-section mb-4">
                    <table class="table table-bordered">
                        <tr class="table-light">
                            <td class="fw-bold" width="70%">Balance as per Bank Statement (Closing)</td>
                            <td class="text-end fw-bold" width="30%">RM {{ number_format($reconciliation->statement_closing_balance, 2) }}</td>
                        </tr>
                    </table>
                </div>
                
                <!-- Unreconciled Transactions -->
                @if($unreconciledItems->count() > 0)
                    @php
                        $unpresentedCheques = $unreconciledItems->where('dc', 'C');
                        $unclearedDeposits = $unreconciledItems->where('dc', 'D');
                    @endphp
                    
                    @if($unpresentedCheques->count() > 0)
                    <div class="unreconciled-section mb-4">
                        <h5 class="section-header">Add: Cheques issued but not presented</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th width="15%">Date</th>
                                        <th width="20%">Entry No</th>
                                        <th width="45%">Particulars</th>
                                        <th width="20%" class="text-end">Amount (RM)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalUnpresented = 0; @endphp
                                    @foreach($unpresentedCheques as $item)
                                        @php $totalUnpresented += $item->amount; @endphp
                                        <tr>
                                            <td>{{ $item->entry->date->format('d-m-Y') }}</td>
                                            <td><code>{{ $item->entry->entry_code }}</code></td>
                                            <td>{{ $item->entry->narration }}</td>
                                            <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-warning">
                                    <tr>
                                        <th colspan="3" class="text-end">Sub Total:</th>
                                        <th class="text-end">RM {{ number_format($totalUnpresented, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @endif
                    
                    @if($unclearedDeposits->count() > 0)
                    <div class="unreconciled-section mb-4">
                        <h5 class="section-header">Less: Deposits in transit / not cleared</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th width="15%">Date</th>
                                        <th width="20%">Entry No</th>
                                        <th width="45%">Particulars</th>
                                        <th width="20%" class="text-end">Amount (RM)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalUncleared = 0; @endphp
                                    @foreach($unclearedDeposits as $item)
                                        @php $totalUncleared += $item->amount; @endphp
                                        <tr>
                                            <td>{{ $item->entry->date->format('d-m-Y') }}</td>
                                            <td><code>{{ $item->entry->entry_code }}</code></td>
                                            <td>{{ $item->entry->narration }}</td>
                                            <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-danger">
                                    <tr>
                                        <th colspan="3" class="text-end">Sub Total:</th>
                                        <th class="text-end">RM {{ number_format($totalUncleared, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @endif
                @endif
                
                <!-- Adjustments -->
                @if($reconciliation->adjustments->count() > 0)
                <div class="adjustments-section mb-4">
                    <h5 class="section-header">Reconciliation Adjustments</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th width="50%">Description</th>
                                    <th width="20%">Type</th>
                                    <th width="30%" class="text-end">Amount (RM)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reconciliation->adjustments as $adjustment)
                                <tr>
                                    <td>{{ $adjustment->description }}</td>
                                    <td>
                                        <span class="badge badge-{{ $adjustment->type == 'debit' ? 'success' : 'danger' }}">
                                            {{ ucfirst($adjustment->type) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if($adjustment->type == 'debit')
                                            +{{ number_format($adjustment->amount, 2) }}
                                        @else
                                            -{{ number_format($adjustment->amount, 2) }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                
                <!-- Final Balance Calculation -->
                <div class="final-balance mb-4">
                    <table class="table table-bordered">
                        <tbody>
                            @php
                                $adjustedBalance = $reconciliation->statement_closing_balance;
                                if(isset($totalUnpresented)) $adjustedBalance += $totalUnpresented;
                                if(isset($totalUncleared)) $adjustedBalance -= $totalUncleared;
                                
                                foreach($reconciliation->adjustments as $adj) {
                                    if($adj->type == 'debit') {
                                        $adjustedBalance += $adj->amount;
                                    } else {
                                        $adjustedBalance -= $adj->amount;
                                    }
                                }
                            @endphp
                            
                            <tr class="table-success">
                                <td class="fw-bold" width="70%">Adjusted Bank Balance</td>
                                <td class="text-end fw-bold" width="30%">RM {{ number_format($adjustedBalance, 2) }}</td>
                            </tr>
                            <tr class="table-primary">
                                <td class="fw-bold">Balance as per Books</td>
                                <td class="text-end fw-bold">RM {{ number_format($reconciliation->reconciled_balance, 2) }}</td>
                            </tr>
                            <tr class="table-{{ abs($reconciliation->difference) > 0.01 ? 'danger' : 'success' }}">
                                <td class="fw-bold">Difference</td>
                                <td class="text-end fw-bold">
                                    RM {{ number_format($reconciliation->difference, 2) }}
                                    @if(abs($reconciliation->difference) <= 0.01)
                                        <i class="fas fa-check-circle text-success ms-2"></i>
                                    @else
                                        <i class="fas fa-exclamation-triangle text-danger ms-2"></i>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
   
                
                <!-- Notes -->
                @if($reconciliation->notes)
                <div class="notes-section mb-4">
                    <h5 class="section-header">Additional Notes:</h5>
                    <div class="card">
                        <div class="card-body">
                            <p class="mb-0">{{ $reconciliation->notes }}</p>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Signature Section -->
                <div class="signature-section mt-5">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="signature-box">
                                <div class="signature-line"></div>
                                <p class="signature-label">
                                    <strong>Prepared By</strong><br>
                                    <small>{{ $reconciliation->creator ? $reconciliation->creator->name : 'System Generated' }}</small><br>
                                    <small class="text-muted">{{ $reconciliation->created_at->format('d M Y') }}</small>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="signature-box">
                                <div class="signature-line"></div>
                                <p class="signature-label">
                                    <strong>Reviewed By</strong><br>
                                    <small>{{ $reconciliation->reconciledBy ? $reconciliation->reconciledBy->name : '_______________' }}</small><br>
                                    <small class="text-muted">{{ $reconciliation->reconciled_date ? $reconciliation->reconciled_date->format('d M Y') : '___________' }}</small>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="signature-box">
                                <div class="signature-line"></div>
                                <p class="signature-label">
                                    <strong>Approved By</strong><br>
                                    <small>_______________</small><br>
                                    <small class="text-muted">___________</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="report-footer text-center mt-4">
                    <hr>
                    <small class="text-muted">
                        Report generated on: {{ now()->format('d M Y \a\t H:i:s') }} | 
                        Status: {{ ucfirst($reconciliation->status) }} |
                        Period: {{ $reconciliation->month_display }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Print Styles */
@media print {
    .no-print {
        display: none !important;
    }
    
    .print-container {
        box-shadow: none !important;
        border: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    body {
        background: white !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .table {
        page-break-inside: avoid;
    }
    
    .signature-section {
        page-break-inside: avoid;
        margin-top: 50px;
    }
}

/* Company Header Styles */
.company-header {
    display: flex;
    align-items: flex-start;
    margin-bottom: 30px;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 20px;
    gap: 20px;
    flex-wrap: wrap;
}

.company-header .logo img {
    width: 120px;
    height: 80px;
    object-fit: contain;
}

.company-header .company-info {
    font-size: 13px;
    line-height: 1.6;
    max-width: 580px;
}

.company-header .company-info strong {
    font-size: 24px;
    color: #e16c2f;
    display: block;
    margin-bottom: 5px;
}

/* Report Header Styles */
.report-header h2 {
    font-weight: bold;
    color: #2c3e50;
    text-decoration: underline;
}

.status-badge {
    margin-top: 10px;
}

/* Section Headers */
.section-header {
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 5px;
    margin-bottom: 15px;
    font-weight: 600;
}

/* Balance Section */
.balance-section .table td {
    font-size: 16px;
    padding: 12px;
}

/* Final Balance */
.final-balance .table td {
    font-size: 16px;
    padding: 12px;
}

/* Signature Section */
.signature-section {
    margin-top: 60px;
}

.signature-box {
    text-align: center;
    margin-bottom: 20px;
}

.signature-line {
    width: 200px;
    height: 50px;
    border-bottom: 1px solid #000;
    margin: 0 auto 10px;
}

.signature-label {
    margin: 0;
    font-size: 12px;
}

.signature-label strong {
    font-size: 14px;
    display: block;
    margin-bottom: 5px;
}

/* Summary Cards */
.summary-section .card {
    height: 100%;
}

/* Table Styles */
.table-bordered {
    border: 2px solid #dee2e6;
}

.table-bordered th,
.table-bordered td {
    border: 1px solid #dee2e6;
}

/* Badge Styles for Print */
.badge {
    color: #000 !important;
    border: 1px solid #000;
    background: transparent !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .company-header {
        flex-direction: column;
        text-align: center;
    }
    
    .company-header .company-info {
        max-width: 100%;
    }
    
    .signature-section .row {
        flex-direction: column;
    }
    
    .signature-box {
        margin-bottom: 30px;
    }
}
</style>
@endpush
@endsection