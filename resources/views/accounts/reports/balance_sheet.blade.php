@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb pl-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> Home</a></li>
                <li class="breadcrumb-item">Reports</li>
                <li class="breadcrumb-item active" aria-current="page">Balance Sheet</li>
            </ol>
        </nav>
<br>
        <div class="ms-panel">
            <div class="card shadow-sm">
                <!-- Header -->
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">
                                <i class="fas fa-file-invoice-dollar"></i> Balance Sheet
                            </h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="opacity-75">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <span style="color: white;">Financial Year: {{ date('d-m-Y', strtotime($activeYear->from_year_month)) }} to {{ date('d-m-Y', strtotime($activeYear->to_year_month)) }}</span>
                            </small>
                        </div>
                    </div>
                </div>


                <div class="card-body">
                    <form method="GET" action="{{ route('accounts.reports.balance-sheet') }}" id="bsReportForm">
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label>Date</label>
                                <input type="date" name="date" class="form-control"
                                    value="{{ $asOnDate }}"
                                    max="{{ date('Y-m-d') }}"
                                    required>
                            </div>

                            <div class="col-md-3 my-4">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-success btn-block">
                                    SUBMIT
                                </button>
                            </div>

                            <div class="col-md-6 my-4 text-end">
                                <label>&nbsp;</label>
                                <div class="btn-group float-right" role="group">
                                    <button type="button" class="btn btn-sm btn-info" onclick="printReport()">
                                        Print
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="exportReport('pdf')">
                                        PDF
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" onclick="exportReport('excel')">
                                        Excel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    @if(!empty($balanceSheetData))
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr style="background-color: #f5f5f5;">
                                    <th width="60%">Account Name</th>
                                    <th width="20%" class="text-right">Current Year</th>
                                    <th width="20%" class="text-right">Previous Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="3" class="p-0">
                                        <div class="accordion" id="balanceSheetAccordion">
                                            @foreach($balanceSheetData as $parentIndex => $parentGroup)
                                            <div class="card">
                                                <div class="card-header p-2" id="heading{{ $parentIndex }}">
                                                    <h2 class="mb-0">
                                                        <button class="btn btn-link btn-block text-left collapsed"
                                                            type="button" data-toggle="collapse"
                                                            data-target="#collapse{{ $parentIndex }}"
                                                            aria-expanded="false"
                                                            aria-controls="collapse{{ $parentIndex }}">
                                                            <strong>({{ $parentGroup['code'] }}) {{ strtoupper($parentGroup['name']) }}</strong>
                                                        </button>
                                                    </h2>
                                                </div>

                                                <div id="collapse{{ $parentIndex }}"
                                                    class="collapse"
                                                    aria-labelledby="heading{{ $parentIndex }}"
                                                    data-parent="#balanceSheetAccordion">
                                                    <div class="card-body p-0">
                                                        <table class="table table-sm mb-0">
                                                            <tbody>
                                                                @include('accounts.reports.partials.balance_sheet_group', [
                                                                'group' => $parentGroup,
                                                                'level' => 0,
                                                                'asOnDate'=>$activeYear->from_year_month,
                                                                'activeYear' => $asOnDate

                                                                ])

                                                                {{-- Add Current P&L for Equity section --}}
                                                                @if($parentGroup['code'] == '3000' && isset($parentGroup['profitLoss']))
                                                                <tr>
                                                                    <td class="indent-1">{{ $parentGroup['profitLoss']['name'] }}</td>
                                                                    <td class="text-right">
                                                                        @if($parentGroup['profitLoss']['current'] > 0)
                                                                        ({{ number_format($parentGroup['profitLoss']['current'], 2) }})
                                                                        @else
                                                                        {{ number_format(abs($parentGroup['profitLoss']['current']), 2) }}
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-right">-</td>
                                                                </tr>
                                                                @endif

                                                                {{-- Group Total --}}
                                                                <tr style="background-color: #f8f9fa; font-weight: bold;">
                                                                    <td class="text-right">
                                                                        TOTAL {{ strtoupper($parentGroup['name']) }}
                                                                    </td>
                                                                    <td class="text-right">
                                                                        @if($parentGroup['currentBalance'] < 0)
                                                                            ({{ number_format(abs($parentGroup['currentBalance']), 2) }})
                                                                            @else
                                                                            {{ number_format($parentGroup['currentBalance'], 2) }}
                                                                            @endif
                                                                            </td>
                                                                    <td class="text-right">
                                                                        @if($parentGroup['previousBalance'] < 0)
                                                                            ({{ number_format(abs($parentGroup['previousBalance']), 2) }})
                                                                            @else
                                                                            {{ number_format($parentGroup['previousBalance'], 2) }}
                                                                            @endif
                                                                            </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #e9ecef; font-weight: bold;">
                                    <td>TOTAL LIABILITIES & EQUITY</td>
                                    <td class="text-right">
                                        @php
                                        $totalLiabEquity = $totalLiabilities['current'] + $totalEquity['current'];
                                        @endphp
                                        @if($totalLiabEquity < 0)
                                            ({{ number_format(abs($totalLiabEquity), 2) }})
                                            @else
                                            {{ number_format($totalLiabEquity, 2) }}
                                            @endif
                                            </td>
                                    <td class="text-right">
                                        @php
                                        $totalLiabEquityPrev = $totalLiabilities['previous'] + $totalEquity['previous'];
                                        @endphp
                                        @if($totalLiabEquityPrev < 0)
                                            ({{ number_format(abs($totalLiabEquityPrev), 2) }})
                                            @else
                                            {{ number_format($totalLiabEquityPrev, 2) }}
                                            @endif
                                            </td>
                                </tr>
                                @php
                                $leftSide = $totalAssets['current'];
                                $rightSide = $totalLiabilities['current'] + $totalEquity['current'];

                                $isBalanced = false;
                                if ($leftSide > 0 && $rightSide < 0) {
                                    $isBalanced=abs($leftSide - abs($rightSide)) < 0.01;
                                    } else if ($leftSide < 0 && $rightSide> 0) {
                                    $isBalanced = abs(abs($leftSide) - $rightSide) < 0.01;
                                        } else {
                                        $isBalanced=abs($leftSide - $rightSide) < 0.01;
                                        }
                                        @endphp
                                        @if(!$isBalanced)
                                        <tr>
                                        <td colspan="3" class="text-center text-danger">
                                            <strong>Balance Sheet is not balanced! Difference: {{ number_format(abs($leftSide - $rightSide), 2) }}</strong>
                                        </td>
                                        </tr>
                                        @endif
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .accordion .card {
            border: none;
            margin-bottom: 0;
        }

        .accordion .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .accordion .btn-link {
            color: #333;
            text-decoration: none;
            font-size: 14px;
        }

        .accordion .btn-link:hover {
            text-decoration: none;
        }

        .indent-1 {
            padding-left: 30px !important;
        }

        .indent-2 {
            padding-left: 60px !important;
        }

        .indent-3 {
            padding-left: 90px !important;
        }

        .group-row {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .ledger-row {
            font-weight: normal;
        }

        /* Ledger link styles */
        .ledger-link {
            color: #007bff;
            text-decoration: none;
            cursor: pointer;
        }

        .ledger-link:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .group-name {
            font-weight: 600;
            color: #333;
        }
    </style>

    <script>
        function exportReport(type) {
            const form = document.getElementById('bsReportForm');
            const formData = new FormData(form);

            let url = '{{ route("accounts.reports.balance-sheet") }}?';
            for (let [key, value] of formData.entries()) {
                url += key + '=' + encodeURIComponent(value) + '&';
            }
            url += 'export=' + type;

            window.location.href = url;
        }

        function printReport() {
            const form = document.getElementById('bsReportForm');
            const formData = new FormData(form);

            let url = '{{ route("accounts.reports.balance-sheet") }}?';
            for (let [key, value] of formData.entries()) {
                url += key + '=' + encodeURIComponent(value) + '&';
            }
            url += 'export=print';

            window.open(url, '_blank');
        }
    </script>
    @endsection