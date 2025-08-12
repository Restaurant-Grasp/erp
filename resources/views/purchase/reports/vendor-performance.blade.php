@extends('layouts.app')

@section('title', 'Vendor Performance Report')

@section('content')
<div class="page-header">
    <h1 class="page-title">Vendor Performance Report</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase.orders.index') }}">Purchases</a></li>
            <li class="breadcrumb-item active">Vendor Performance</li>
        </ol>
    </nav>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Report Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('purchase.reports.vendor-performance') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="start_date" value="{{ @$startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="end_date" value="{{ @$endDate }}">
                </div>
                <div class="col-md-4">
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
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Performance Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center bg-primary text-white">
            <div class="card-body">
                <i class="fas fa-building fa-2x mb-2"></i>
                <h5 class="card-title">Active Vendors</h5>
                <h3>{{ isset($vendorPerformance) ? $vendorPerformance->count() : 0 }}</h3>

                <small>With purchase activity</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-success text-white">
            <div class="card-body">
                <i class="fas fa-star fa-2x mb-2"></i>
                <h5 class="card-title">Top Performer</h5>
                @if(isset($vendorPerformance) && $vendorPerformance->isNotEmpty())
                @php
                $topPerformer = $vendorPerformance->sortByDesc('performance_score')->first();
                @endphp
                <h3>{{ number_format($topPerformer->performance_score, 1) }}%</h3>
                <small>{{ $topPerformer->company_name }}</small>
                @else
                <h3>-</h3>
                <small>No data</small>
                @endif

            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-info text-white">
            <div class="card-body">
                <i class="fas fa-truck fa-2x mb-2"></i>
                <h5 class="card-title">Avg Delivery Time</h5>
                <h3>
                    {{ isset($vendorPerformance) && $vendorPerformance->isNotEmpty()
        ? number_format($vendorPerformance->avg('avg_delivery_days'), 1)
        : 'No data available' }}
                </h3>

                <small>Days</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-warning text-white">
            <div class="card-body">
                <i class="fas fa-undo fa-2x mb-2"></i>
                <h5 class="card-title">Avg Return Rate</h5>
                @if(isset($vendorPerformance) && $vendorPerformance->isNotEmpty())
                <h3>{{ number_format($vendorPerformance->avg('return_percentage'), 1) }}%</h3>
                @else
                <h3>No data available</h3>
                @endif

                <small>Of purchase value</small>
            </div>
        </div>
    </div>
</div>

<!-- Performance Chart -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-radar me-2"></i>Vendor Performance Comparison</h5>
    </div>
    <div class="card-body">
        <canvas id="performanceChart" height="400"></canvas>
    </div>
</div>

<!-- Detailed Performance Table -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-table me-2"></i>Detailed Vendor Performance</h5>
        <div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="sortTable('performance_score')">
                <i class="fas fa-sort me-1"></i>Sort by Performance
            </button>
            <button type="button" class="btn btn-sm btn-outline-success" onclick="sortTable('total_purchase_amount')">
                <i class="fas fa-dollar-sign me-1"></i>Sort by Amount
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="performanceTable">
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Total POs</th>
                        <th>Purchase Amount</th>
                        <th>Avg Order Value</th>
                        <th>Completion Rate</th>
                        <th>On-Time Delivery</th>
                        <th>Return Rate</th>
                        <th>Avg Delivery Days</th>
                        <th>Performance Score</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($vendorPerformance) && $vendorPerformance->isNotEmpty())
                    @forelse($vendorPerformance as $vendor)
                    <tr>
                        <td>
                            <strong>{{ $vendor->company_name }}</strong>
                            <br><small class="text-muted">{{ $vendor->vendor_code }}</small>
                        </td>
                        <td>{{ number_format($vendor->total_pos) }}</td>
                        <td>RM {{ number_format($vendor->total_purchase_amount, 2) }}</td>
                        <td>RM {{ number_format($vendor->avg_order_value, 2) }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: {{ $vendor->completion_rate }}%">
                                        {{ number_format($vendor->completion_rate, 1) }}%
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                    <div class="progress-bar bg-info" role="progressbar"
                                        style="width: {{ $vendor->on_time_delivery_rate }}%">
                                        {{ number_format($vendor->on_time_delivery_rate, 1) }}%
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $vendor->return_percentage > 5 ? 'danger' : ($vendor->return_percentage > 2 ? 'warning' : 'success') }}">
                                {{ number_format($vendor->return_percentage, 1) }}%
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $vendor->avg_delivery_days > 7 ? 'danger' : ($vendor->avg_delivery_days > 3 ? 'warning' : 'success') }}">
                                {{ number_format($vendor->avg_delivery_days, 1) }} days
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 25px;">
                                    <div class="progress-bar bg-{{ $vendor->performance_score >= 80 ? 'success' : ($vendor->performance_score >= 60 ? 'warning' : 'danger') }}"
                                        role="progressbar" style="width: {{ $vendor->performance_score }}%">
                                        {{ number_format($vendor->performance_score, 1) }}%
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                            $rating = $vendor->performance_score >= 90 ? 5 :
                            ($vendor->performance_score >= 80 ? 4 :
                            ($vendor->performance_score >= 70 ? 3 :
                            ($vendor->performance_score >= 60 ? 2 : 1)));
                            @endphp
                            <div class="text-warning">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star{{ $i <= $rating ? '' : '-o' }}"></i>
                                    @endfor
                            </div>
                            <small class="text-muted">{{ $rating }}/5</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                <p>No vendor performance data available for the selected period</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                    @else
                    <p>No vendor performance data available.</p>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Performance Categories -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-trophy me-2"></i>Excellent Performers (90%+)</h6>
            </div>
            <div class="card-body">
                @php
                $excellent = collect(); // default empty collection
                if (isset($vendorPerformance) && $vendorPerformance->isNotEmpty()) {
                $excellent = $vendorPerformance->where('performance_score', '>=', 90);
                }
                @endphp

                @if($excellent->count() > 0)
                @foreach($excellent->take(5) as $vendor)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <strong>{{ $vendor->company_name }}</strong>
                        <br><small class="text-muted">{{ number_format($vendor->performance_score, 1) }}% score</small>
                    </div>
                    <span class="badge bg-success">{{ number_format($vendor->total_purchase_amount, 0) }}</span>
                </div>
                @endforeach
                @else
                <p class="text-muted mb-0">No vendors in this category</p>
                @endif

            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h6 class="mb-0"><i class="fas fa-star me-2"></i>Good Performers (70-89%)</h6>
            </div>
            <div class="card-body">
                @php
                $good = collect(); // default empty collection
                if (isset($vendorPerformance) && $vendorPerformance->isNotEmpty()) {
                $good = $vendorPerformance->whereBetween('performance_score', [70, 89]);
                }
                @endphp

                @if($good->count() > 0)
                @foreach($good->take(5) as $vendor)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <strong>{{ $vendor->company_name }}</strong>
                        <br><small class="text-muted">{{ number_format($vendor->performance_score, 1) }}% score</small>
                    </div>
                    <span class="badge bg-warning">{{ number_format($vendor->total_purchase_amount, 0) }}</span>
                </div>
                @endforeach
                @else
                <p class="text-muted mb-0">No vendors in this category</p>
                @endif

            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Needs Improvement (<70%)< /h6>
            </div>
            <div class="card-body">
                @php
                $poor = collect(); // default empty collection
                if (isset($vendorPerformance) && $vendorPerformance->isNotEmpty()) {
                $poor = $vendorPerformance->where('performance_score', '<', 70);
                    }
                    @endphp

                    @if($poor->count() > 0)
                    @foreach($poor->take(5) as $vendor)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <strong>{{ $vendor->company_name }}</strong>
                            <br><small class="text-muted">{{ number_format($vendor->performance_score, 1) }}% score</small>
                        </div>
                        <span class="badge bg-danger">{{ number_format($vendor->total_purchase_amount, 0) }}</span>
                    </div>
                    @endforeach
                    @else
                    <p class="text-muted mb-0">No vendors in this category</p>
                    @endif

            </div>
        </div>
    </div>
</div>

<!-- Action Items -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Recommended Actions</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-success"><i class="fas fa-thumbs-up me-2"></i>Recognize & Reward</h6>
                <ul class="list-unstyled">
                    @php
                    $topVendors = collect();
                    if (isset($vendorPerformance) && $vendorPerformance->isNotEmpty()) {
                    $topVendors = $vendorPerformance->where('performance_score', '>=', 90)->take(3);
                    $topVendorsCount = $vendorPerformance->where('performance_score', '>=', 90)->count();
                    } else {
                    $topVendorsCount = 0;
                    }
                    @endphp

                    @if($topVendorsCount > 0)
                    @foreach($topVendors as $vendor)
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        <strong>{{ $vendor->company_name }}</strong> - Consider preferred vendor status
                    </li>
                    @endforeach
                    @else
                    <li class="text-muted">No vendors currently qualify for recognition</li>
                    @endif

                </ul>
            </div>
            <div class="col-md-6">
                <h6 class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Monitor & Improve</h6>
                <ul class="list-unstyled">
                    @php
                    $poorVendors = collect();
                    if (isset($vendorPerformance) && $vendorPerformance->isNotEmpty()) {
                    $poorVendors = $vendorPerformance->where('performance_score', '<', 70)->take(3);
                        $poorVendorsCount = $vendorPerformance->where('performance_score', '<', 70)->count();
                            } else {
                            $poorVendorsCount = 0;
                            }
                            @endphp

                            @if($poorVendorsCount > 0)
                            @foreach($poorVendors as $vendor)
                            <li class="mb-2">
                                <i class="fas fa-eye text-warning me-2"></i>
                                <strong>{{ $vendor->company_name }}</strong> - Schedule performance review
                            </li>
                            @endforeach
                            @else
                            <li class="text-muted">No vendors currently need immediate attention</li>
                            @endif

                </ul>
            </div>
        </div>
    </div>
</div>
@php
    $topPerformers = collect();
    if (isset($vendorPerformance) && $vendorPerformance->isNotEmpty()) {
        $topPerformers = $vendorPerformance->sortByDesc('performance_score')->take(5)->values();
    }
@endphp
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Performance Radar Chart
        const performanceCtx = document.getElementById('performanceChart').getContext('2d');

        // Get top 5 performers for chart
  const topPerformers = @json($topPerformers);
   
        const performanceChart = new Chart(performanceCtx, {
            type: 'radar',
            data: {
                labels: ['Completion Rate', 'On-Time Delivery', 'Low Return Rate', 'Quick Delivery', 'Order Volume'],
                datasets: topPerformers.map((vendor, index) => ({
                    label: vendor.company_name,
                    data: [
                        vendor.completion_rate,
                        vendor.on_time_delivery_rate,
                        Math.max(0, 100 - vendor.return_percentage), // Invert return rate
                        Math.max(0, 100 - Math.min(vendor.avg_delivery_days * 10, 100)), // Invert delivery days
                        Math.min(vendor.total_pos * 2, 100) // Scale order volume
                    ],
                    borderColor: `hsl(${index * 72}, 70%, 50%)`,
                    backgroundColor: `hsla(${index * 72}, 70%, 50%, 0.1)`,
                    pointBackgroundColor: `hsl(${index * 72}, 70%, 50%)`,
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: `hsl(${index * 72}, 70%, 50%)`
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 20
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });

    function sortTable(column) {
        const table = document.getElementById('performanceTable');
        const tbody = table.getElementsByTagName('tbody')[0];
        const rows = Array.from(tbody.getElementsByTagName('tr'));

        // Remove "no data" row if present
        const filteredRows = rows.filter(row => row.cells.length > 1);

        filteredRows.sort((a, b) => {
            let aValue, bValue;

            switch (column) {
                case 'performance_score':
                    aValue = parseFloat(a.cells[8].textContent.replace('%', ''));
                    bValue = parseFloat(b.cells[8].textContent.replace('%', ''));
                    break;
                case 'total_purchase_amount':
                    aValue = parseFloat(a.cells[2].textContent.replace(/[RM,]/g, ''));
                    bValue = parseFloat(b.cells[2].textContent.replace(/[RM,]/g, ''));
                    break;
                default:
                    return 0;
            }

            return bValue - aValue; // Descending order
        });

        // Clear tbody and re-add sorted rows
        tbody.innerHTML = '';
        filteredRows.forEach(row => tbody.appendChild(row));

        // Add visual feedback
        const buttons = document.querySelectorAll('[onclick^="sortTable"]');
        buttons.forEach(btn => btn.classList.remove('btn-primary'));
        event.target.classList.add('btn-primary');
    }

    // Auto-submit form on input change
    $('input[name="start_date"], input[name="end_date"], select[name="vendor_id"]').on('change', function() {
        $('#filterForm').submit();
    });
</script>

<style>
    .progress {
        background-color: #e9ecef;
    }

    .table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }

    .card-header.bg-success,
    .card-header.bg-warning,
    .card-header.bg-danger {
        border-bottom: none;
    }

    .border-bottom:last-child {
        border-bottom: none !important;
    }

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
            margin-bottom: 20px;
        }

        canvas {
            max-height: 300px !important;
        }
    }

    .fa-star {
        color: #ffc107;
    }

    .fa-star-o {
        color: #e9ecef;
    }

    .performance-badge {
        font-size: 0.9em;
        padding: 0.5em 0.75em;
    }
</style>
@endsection