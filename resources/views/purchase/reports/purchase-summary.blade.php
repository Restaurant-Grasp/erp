@extends('layouts.app')

@section('title', 'Purchase Summary Report')

@section('content')
<div class="page-header">
    <h1 class="page-title">Purchase Summary Report</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.orders.index') }}">Purchases</a></li>
            <li class="breadcrumb-item active">Purchase Summary</li>
        </ol>
    </nav>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Report Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('purchase.reports.purchase-summary') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="start_date" value="{{ @$startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="end_date" value="{{ @$endDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vendor</label>
                    <select class="form-select" name="vendor_id">
                        <option value="">All Vendors</option>
                        @if(isset($vendors))
                        @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ $vendorId == $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->company_name }}
                        </option>
                        @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="draft" {{ ($status ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="approved" {{ ($status ?? '') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="partial" {{ ($status ?? '') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="received" {{ ($status ?? '') == 'received' ? 'selected' : '' }}>Received</option>

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
                <i class="fas fa-file-alt fa-2x mb-2"></i>
                <h5 class="card-title">Total Purchase Orders</h5>
                <h3>{{ number_format($poSummary['total_pos'] ?? 0) }}</h3>
                <small>RM {{ number_format($poSummary['total_amount'] ?? 0, 2) }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-warning text-white">
            <div class="card-body">
                <i class="fas fa-clock fa-2x mb-2"></i>
                <h5 class="card-title">Pending Approval</h5>
                <h3>{{ number_format($poSummary['pending_approval'] ?? 0) }}</h3>
                <small>
                    {{ ($poSummary['total_pos'] ?? 0) > 0 
        ? number_format((($poSummary['pending_approval'] ?? 0) / $poSummary['total_pos']) * 100, 1) 
        : 0 
    }}%
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-success text-white">
            <div class="card-body">
                <i class="fas fa-check-circle fa-2x mb-2"></i>
                <h5 class="card-title">Fully Received</h5>
                <h3>{{ number_format($poSummary['fully_received'] ?? 0) }}</h3>
                <small>
                    {{ ($poSummary['total_pos'] ?? 0) > 0 
        ? number_format((($poSummary['fully_received'] ?? 0) / $poSummary['total_pos']) * 100, 1) 
        : 0 
    }}%
                </small>

            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-info text-white">
            <div class="card-body">
                <i class="fas fa-truck fa-2x mb-2"></i>
                <h5 class="card-title">Partially Received</h5>
                <h3>{{ number_format($poSummary['partially_received'] ?? 0) }}</h3>
                <small>
                    {{ ($poSummary['total_pos'] ?? 0) > 0 
        ? number_format((($poSummary['partially_received'] ?? 0) / $poSummary['total_pos']) * 100, 1) 
        : 0 
    }}%
                </small>

            </div>
        </div>
    </div>
</div>

<!-- Invoice Summary -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center bg-secondary text-white">
            <div class="card-body">
                <i class="fas fa-file-invoice fa-2x mb-2"></i>
                <h5 class="card-title">Total Invoices</h5>
                <h3>{{ number_format($invoiceSummary['total_invoices'] ?? 0) }}</h3>
                <small>RM {{ number_format($invoiceSummary['total_amount'] ?? 0, 2) }}</small>

            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-success text-white">
            <div class="card-body">
                <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                <h5 class="card-title">Paid Amount</h5>
                <h3>RM {{ number_format($invoiceSummary['paid_amount'] ?? 0, 2) }}</h3>
                <small>
                    {{ ($invoiceSummary['total_amount'] ?? 0) > 0 
        ? number_format((($invoiceSummary['paid_amount'] ?? 0) / $invoiceSummary['total_amount']) * 100, 1) 
        : 0 
    }}%
                </small>

            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-danger text-white">
            <div class="card-body">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <h5 class="card-title">Outstanding</h5>
                <h3>RM {{ number_format($invoiceSummary['outstanding_amount'] ?? 0, 2) }}</h3>
                <small>{{ $invoiceSummary['overdue_invoices'] ?? 0 }} Overdue</small>

            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-dark text-white">
            <div class="card-body">
                <i class="fas fa-exchange-alt fa-2x mb-2"></i>
                <h5 class="card-title">Direct vs PO Conversion</h5>
                <h3>{{ $invoiceSummary['direct_invoices'] ?? 0 }} / {{ $invoiceSummary['po_conversion_invoices'] ?? 0 }}</h3>
                <small>Direct / Conversion</small>

            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Monthly Trend Chart -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Purchase Trend (Last 12 Months)</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyTrendChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Vendors Chart -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Top 5 Vendors by Amount</h5>
            </div>
            <div class="card-body">
                <canvas id="topVendorsChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Vendors Table -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Vendors by Purchase Amount</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Vendor</th>
                        <th>Total POs</th>
                        <th>Total Amount</th>
                        <th>Average Order Value</th>
                        <th>Performance</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($topVendors) && $topVendors->count() > 0)
                    @forelse($topVendors as $index => $vendor)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $vendor->company_name }}</strong>
                            <br><small class="text-muted">{{ $vendor->vendor_code }}</small>
                        </td>
                        <td>{{ number_format($vendor->total_pos) }}</td>
                        <td>RM {{ number_format($vendor->total_amount, 2) }}</td>
                        <td>RM {{ number_format($vendor->total_amount / $vendor->total_pos, 2) }}</td>
                        <td>
                            @php
                            $percentage = $poSummary['total_amount'] > 0 ? ($vendor->total_amount / $poSummary['total_amount']) * 100 : 0;
                            @endphp
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-primary" role="progressbar"
                                    style="width: {{ min($percentage, 100) }}%"
                                    title="{{ number_format($percentage, 1) }}% of total purchases">
                                    {{ number_format($percentage, 1) }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">No vendor data available for the selected period</div>
                        </td>
                    </tr>
                    @endforelse
                    @else
                    <tr>
                        <td colspan="6" class="text-center">No vendor data available</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Category Analysis -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Purchase by Product Category</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Total POs</th>
                        <th>Total Quantity</th>
                        <th>Total Amount</th>
                        <th>% of Total Purchases</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($categoryAnalysis) && $categoryAnalysis->count() > 0)
                    @forelse($categoryAnalysis as $category)
                    <tr>
                        <td><strong>{{ $category->category_name }}</strong></td>
                        <td>{{ number_format($category->total_pos) }}</td>
                        <td>{{ number_format($category->total_quantity, 2) }}</td>
                        <td>RM {{ number_format($category->total_amount, 2) }}</td>
                        <td>
                            @php
                            $categoryPercentage = $poSummary['total_amount'] > 0 ? ($category->total_amount / $poSummary['total_amount']) * 100 : 0;
                            @endphp
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                    style="width: {{ min($categoryPercentage, 100) }}%">
                                    {{ number_format($categoryPercentage, 1) }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="text-muted">No category data available for the selected period</div>
                        </td>
                    </tr>
                    @endforelse
                    @else
    <tr>
        <td colspan="5" class="text-center">No category data available</td>
    </tr>
@endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Export Options -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-download me-2"></i>Export Options</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <button type="button" class="btn btn-success me-2" onclick="exportToExcel()">
                    <i class="fas fa-file-excel me-2"></i>Export to Excel
                </button>
                <button type="button" class="btn btn-danger me-2" onclick="exportToPDF()">
                    <i class="fas fa-file-pdf me-2"></i>Export to PDF
                </button>
                <button type="button" class="btn btn-info" onclick="printReport()">
                    <i class="fas fa-print me-2"></i>Print Report
                </button>
            </div>
            <div class="col-md-6 text-end">
                <small class="text-muted">
                    Report generated on {{ now()->format('d/m/Y H:i:s') }}<br>
                    @if(!empty($startDate) && !empty($endDate))
                    Period: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                    @else
                    Period: N/A
                    @endif
                </small>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    @if(isset($monthlyTrend) && $monthlyTrend->isNotEmpty())
    // Monthly Trend Chart
    const monthlyTrendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
    const monthlyTrendChart = new Chart(monthlyTrendCtx, {
        type: 'line',
        data: {
            labels: @json($monthlyTrend->pluck('month')),
            datasets: [{
                label: 'Number of POs',
                data: @json($monthlyTrend->pluck('total_pos')),
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                yAxisID: 'y'
            }, {
                label: 'Total Amount (RM)',
                data: @json($monthlyTrend->pluck('total_amount')),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Number of POs'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Amount (RM)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
    @else
    // Optionally: Show message or empty chart if no data
    console.log('No monthlyTrend data available');
    @endif

    @if(isset($topVendors) && $topVendors->isNotEmpty())
    // Top Vendors Pie Chart
    const topVendorsCtx = document.getElementById('topVendorsChart').getContext('2d');
    const topVendorsChart = new Chart(topVendorsCtx, {
        type: 'doughnut',
        data: {
            labels: @json($topVendors->take(5)->pluck('company_name')),
            datasets: [{
                data: @json($topVendors->take(5)->pluck('total_amount')),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ]
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
    @else
    console.log('No topVendors data available');
    @endif
});
    function exportToExcel() {
        const url = new URL(window.location.href);
        url.pathname = url.pathname.replace('/purchase-summary', '/export-purchase-summary');
        url.searchParams.set('format', 'excel');

        window.open(url.toString(), '_blank');
    }

    function exportToPDF() {
        window.print();
    }

    function printReport() {
        window.print();
    }

    // Auto-submit form on date change
    $('input[name="start_date"], input[name="end_date"], select[name="vendor_id"], select[name="status"]').on('change', function() {
        $('#filterForm').submit();
    });
</script>

<style>
    @media print {
        .card-header {
            background-color: #f8f9fa !important;
            -webkit-print-color-adjust: exact;
        }

        .btn,
        .breadcrumb,
        .page-header nav {
            display: none !important;
        }

        .card {
            border: 1px solid #dee2e6 !important;
            break-inside: avoid;
        }
    }
</style>
@endsection