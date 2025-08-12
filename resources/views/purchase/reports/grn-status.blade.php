@extends('layouts.app')

@section('title', 'GRN Status Report')

@section('content')
<div class="page-header">
    <h1 class="page-title">Goods Receipt Notes (GRN) Status Report</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.orders.index') }}">Purchases</a></li>
            <li class="breadcrumb-item active">GRN Status</li>
        </ol>
    </nav>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Report Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('purchase.reports.grn-status') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="start_date" value="{{ $startDate ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="end_date" value="{{ $endDate ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vendor</label>
                    <select class="form-select" name="vendor_id">
                        <option value="">All Vendors</option>
                        @foreach($vendors ?? [] as $vendor)
                        <option value="{{ $vendor->id }}" {{ ($vendorId ?? '') == $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->company_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="draft" {{ ($status ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="partial" {{ ($status ?? '') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="completed" {{ ($status ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center bg-primary text-white">
            <div class="card-body">
                <i class="fas fa-truck-loading fa-2x mb-2"></i>
                <h5 class="card-title">Total GRNs</h5>
                <h3>{{ number_format($grnSummary['total_grns'] ?? 0) }}</h3>
                <small>{{ number_format($grnSummary['total_items_received'] ?? 0) }} items received</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-success text-white">
            <div class="card-body">
                <i class="fas fa-check-circle fa-2x mb-2"></i>
                <h5 class="card-title">Completed</h5>
                <h3>{{ number_format($grnSummary['completed_grns'] ?? 0) }}</h3>
                <small>{{ ($grnSummary['total_grns'] ?? 0) > 0 ? number_format((($grnSummary['completed_grns'] ?? 0) / $grnSummary['total_grns']) * 100, 1) : 0 }}% completion rate</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-warning text-white">
            <div class="card-body">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <h5 class="card-title">Damaged Items</h5>
                <h3>{{ number_format($grnSummary['total_items_damaged'] ?? 0) }}</h3>
                <small>{{ number_format($grnSummary['total_returns_created'] ?? 0) }} returns created</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-info text-white">
            <div class="card-body">
                <i class="fas fa-hourglass-half fa-2x mb-2"></i>
                <h5 class="card-title">Partial GRNs</h5>
                <h3>{{ number_format($grnSummary['partial_grns'] ?? 0) }}</h3>
                <small>Require completion</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- GRN Status Distribution -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>GRN Status Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="grnStatusChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Serial Number Status -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-barcode me-2"></i>Serial Number Status</h5>
            </div>
            <div class="card-body">
                @if(isset($serialNumberSummary) && $serialNumberSummary && $serialNumberSummary->total_serials > 0)
                <canvas id="serialStatusChart" height="300"></canvas>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-barcode fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No serial number data available for the selected period</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Serial Number Summary -->
@if(isset($serialNumberSummary) && $serialNumberSummary && $serialNumberSummary->total_serials > 0)
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-barcode fa-2x text-primary mb-2"></i>
                <h6 class="card-title">Total Serials</h6>
                <h4>{{ number_format($serialNumberSummary->total_serials ?? 0) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                <h6 class="card-title">Active Warranty</h6>
                <h4>{{ number_format($serialNumberSummary->active_warranty ?? 0) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-warehouse fa-2x text-info mb-2"></i>
                <h6 class="card-title">In Stock</h6>
                <h4>{{ number_format($serialNumberSummary->in_stock ?? 0) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-shopping-cart fa-2x text-warning mb-2"></i>
                <h6 class="card-title">Sold</h6>
                <h4>{{ number_format($serialNumberSummary->sold ?? 0) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-undo fa-2x text-danger mb-2"></i>
                <h6 class="card-title">Returned</h6>
                <h4>{{ number_format($serialNumberSummary->returned ?? 0) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-times-circle fa-2x text-secondary mb-2"></i>
                <h6 class="card-title">Void Warranty</h6>
                <h4>{{ number_format($serialNumberSummary->void_warranty ?? 0) }}</h4>
            </div>
        </div>
    </div>
</div>
@endif

<!-- GRN List -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>GRN Details</h5>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="filterByStatus('completed')">
                <i class="fas fa-check me-1"></i>Completed
            </button>
            <button type="button" class="btn btn-sm btn-outline-warning" onclick="filterByStatus('partial')">
                <i class="fas fa-hourglass-half me-1"></i>Partial
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="filterByStatus('draft')">
                <i class="fas fa-edit me-1"></i>Draft
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="grnTable">
                <thead>
                    <tr>
                        <th>GRN No</th>
                        <th>Date</th>
                        <th>Vendor</th>
                        <th>PO Reference</th>
                        <th>Status</th>
                        <th>Items Received</th>
                        <th>Items Damaged</th>
                        <th>Received By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($grns ?? [] as $grn)
                    <tr class="grn-status-{{ $grn->status }}">
                        <td>
                            <a href="{{ route('purchase.grn.show', $grn) }}" class="text-decoration-none">
                                <strong>{{ $grn->grn_no }}</strong>
                            </a>
                        </td>
                        <td>{{ $grn->grn_date->format('d/m/Y') }}</td>
                        <td>
                            <strong>{{ $grn->vendor->company_name }}</strong>
                            <br><small class="text-muted">{{ $grn->vendor->vendor_code }}</small>
                        </td>
                        <td>
                            @if($grn->purchaseOrder)
                            <a href="{{ route('purchase.orders.show', $grn->purchaseOrder) }}" class="text-decoration-none">
                                {{ $grn->purchaseOrder->po_no }}
                            </a>
                            @elseif($grn->purchaseInvoice)
                            <a href="{{ route('purchase.invoices.show', $grn->purchaseInvoice) }}" class="text-decoration-none">
                                {{ $grn->purchaseInvoice->invoice_no }}
                            </a>
                            @else
                            <span class="text-muted">Direct GRN</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $grn->status_badge }}">
                                {{ ucfirst($grn->status) }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ number_format($grn->total_accepted_quantity ?? 0, 2) }}</strong>
                            @if(($grn->total_quantity ?? 0) != ($grn->total_accepted_quantity ?? 0))
                            <br><small class="text-muted">of {{ number_format($grn->total_quantity ?? 0, 2) }} total</small>
                            @endif
                        </td>
                        <td>
                            @if(($grn->total_damaged_quantity ?? 0) > 0)
                            <span class="badge bg-danger">{{ number_format($grn->total_damaged_quantity, 2) }}</span>
                            @if($grn->returns->count() > 0)
                            <br><small class="text-info">{{ $grn->returns->count() }} return(s)</small>
                            @endif
                            @else
                            <span class="text-muted">None</span>
                            @endif
                        </td>
                        <td>
                            {{ $grn->receivedBy->name ?? 'N/A' }}
                            <br><small class="text-muted">{{ $grn->grn_date->format('H:i') }}</small>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                @can('purchases.grn.view')
                                <a href="{{ route('purchase.grn.show', $grn) }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                
                                @if($grn->returns->count() > 0)
                                <a href="{{ route('purchase.returns.show', $grn->returns->first()) }}" 
                                   class="btn btn-sm btn-outline-warning" title="View Returns">
                                    <i class="fas fa-undo"></i>
                                </a>
                                @endif
                                
                                @if($grn->status === 'partial')
                                <span class="badge bg-warning ms-1" title="Partial GRN - Needs completion">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-truck-loading fa-3x mb-3"></i>
                                <p>No GRN data available for the selected period</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pending GRNs -->
@if(isset($pendingGrns) && $pendingGrns->count() > 0)
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0 text-warning"><i class="fas fa-clock me-2"></i>Pending GRNs (Approved POs awaiting receipt)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Vendor</th>
                        <th>PO Date</th>
                        <th>Total Amount</th>
                        <th>Received %</th>
                        <th>Days Since Approval</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingGrns as $po)
                    <tr>
                        <td>
                            <a href="{{ route('purchase.orders.show', $po) }}" class="text-decoration-none">
                                <strong>{{ $po->po_no }}</strong>
                            </a>
                        </td>
                        <td>{{ $po->vendor->company_name }}</td>
                        <td>{{ $po->po_date->format('d/m/Y') }}</td>
                        <td>{{ $po->currency ?? 'RM' }} {{ number_format($po->total_amount ?? 0, 2) }}</td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-{{ ($po->received_percentage ?? 0) > 50 ? 'success' : 'warning' }}" 
                                     role="progressbar" style="width: {{ $po->received_percentage ?? 0 }}%">
                                    {{ number_format($po->received_percentage ?? 0, 1) }}%
                                </div>
                            </div>
                        </td>
                        <td>
                            @php $daysSince = $po->approved_date ? $po->approved_date->diffInDays(now()) : 0; @endphp
                            <span class="badge bg-{{ $daysSince > 7 ? 'danger' : ($daysSince > 3 ? 'warning' : 'info') }}">
                                {{ $daysSince }} days
                            </span>
                        </td>
                        <td>
                            @can('purchases.grn.create')
                            <a href="{{ route('purchase.grn.create') }}?po_id={{ $po->id }}" 
                               class="btn btn-sm btn-success" title="Create GRN">
                                <i class="fas fa-plus me-1"></i>Create GRN
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Vendor GRN Efficiency -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Vendor GRN Efficiency</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Total GRNs</th>
                        <th>Completed GRNs</th>
                        <th>Completion Rate</th>
                        <th>Avg GRN Delay</th>
                        <th>Total Returns</th>
                        <th>Efficiency Score</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($grnEfficiency ?? [] as $vendor)
                    <tr>
                        <td>
                            <strong>{{ $vendor->company_name }}</strong>
                            <br><small class="text-muted">{{ $vendor->vendor_code }}</small>
                        </td>
                        <td>{{ number_format($vendor->total_grns ?? 0) }}</td>
                        <td>{{ number_format($vendor->completed_grns ?? 0) }}</td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-{{ ($vendor->completion_rate ?? 0) > 80 ? 'success' : (($vendor->completion_rate ?? 0) > 60 ? 'warning' : 'danger') }}" 
                                     role="progressbar" style="width: {{ $vendor->completion_rate ?? 0 }}%">
                                    {{ number_format($vendor->completion_rate ?? 0, 1) }}%
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ ($vendor->avg_grn_delay_days ?? 0) > 5 ? 'danger' : (($vendor->avg_grn_delay_days ?? 0) > 2 ? 'warning' : 'success') }}">
                                {{ number_format($vendor->avg_grn_delay_days ?? 0, 1) }} days
                            </span>
                        </td>
                        <td>
                            @if(($vendor->total_returns ?? 0) > 0)
                            <span class="badge bg-warning">{{ $vendor->total_returns }}</span>
                            @else
                            <span class="text-muted">None</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $efficiency = (($vendor->completion_rate ?? 0) * 0.6) + (max(0, 100 - (($vendor->avg_grn_delay_days ?? 0) * 10)) * 0.3) + (max(0, 100 - (($vendor->total_returns ?? 0) * 5)) * 0.1);
                            @endphp
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-{{ $efficiency > 80 ? 'success' : ($efficiency > 60 ? 'warning' : 'danger') }}" 
                                     role="progressbar" style="width: {{ $efficiency }}%">
                                    {{ number_format($efficiency, 1) }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="text-muted">No vendor efficiency data available</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // GRN Status Chart
    const grnStatusCtx = document.getElementById('grnStatusChart').getContext('2d');
    const grnStatusChart = new Chart(grnStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Partial', 'Draft'],
            datasets: [{
                data: [
                    {{ $grnSummary['completed_grns'] ?? 0 }},
                    {{ $grnSummary['partial_grns'] ?? 0 }},
                    {{ $grnSummary['draft_grns'] ?? 0 }}
                ],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#6c757d'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    @if(isset($serialNumberSummary) && $serialNumberSummary && $serialNumberSummary->total_serials > 0)
    // Serial Number Status Chart
    const serialStatusCtx = document.getElementById('serialStatusChart').getContext('2d');
    const serialStatusChart = new Chart(serialStatusCtx, {
        type: 'bar',
        data: {
            labels: ['In Stock', 'Sold', 'Returned', 'Active Warranty', 'Expired Warranty', 'Void Warranty'],
            datasets: [{
                label: 'Count',
                data: [
                    {{ $serialNumberSummary->in_stock ?? 0 }},
                    {{ $serialNumberSummary->sold ?? 0 }},
                    {{ $serialNumberSummary->returned ?? 0 }},
                    {{ $serialNumberSummary->active_warranty ?? 0 }},
                    {{ $serialNumberSummary->expired_warranty ?? 0 }},
                    {{ $serialNumberSummary->void_warranty ?? 0 }}
                ],
                backgroundColor: [
                    '#17a2b8',
                    '#ffc107',
                    '#dc3545',
                    '#28a745',
                    '#6c757d',
                    '#343a40'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    @endif
});

function filterByStatus(status) {
    const rows = document.querySelectorAll('#grnTable tbody tr');
    
    rows.forEach(row => {
        if (status === 'all' || row.classList.contains(`grn-status-${status}`)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update button states
    document.querySelectorAll('[onclick^="filterByStatus"]').forEach(btn => {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline-primary');
    });
    
    event.target.classList.remove('btn-outline-primary');
    event.target.classList.add('btn-primary');
}

// Auto-submit form on filter change
$('input[name="start_date"], input[name="end_date"], select[name="vendor_id"], select[name="status"]').on('change', function() {
    $('#filterForm').submit();
});
</script>

<style>
.grn-status-completed {
    border-left: 4px solid #28a745;
}

.grn-status-partial {
    border-left: 4px solid #ffc107;
}

.grn-status-draft {
    border-left: 4px solid #6c757d;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.025);
}

@media print {
    .btn, .breadcrumb, .page-header nav {
        display: none !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
        break-inside: avoid;
        margin-bottom: 20px;
    }
    
    canvas {
        max-height: 200px !important;
    }
}
</style>
@endsection