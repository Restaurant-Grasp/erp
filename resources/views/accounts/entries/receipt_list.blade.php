@extends('layouts.app')

@section('content')

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

<style>

    /* Action Buttons */
    .btn-modern {
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        margin: 2px;
    }


    .btn-modern-lg {
        padding: 12px 24px;
        font-size: 16px;
    }
.btn-info-modern:hover,
.btn-info-modern:focus,
.btn-info-modern:active,
 .btn-info-modern {
    background-color: #17a2b8; /* Bootstrap info */
    color: white;
    border: none;
}
.btn-warning-modern:hover,
.btn-warning-modern:focus,
.btn-warning-modern:active,
.btn-warning-modern {
    background-color: #fd7e14; /* Bootstrap warning */
    color: white;
    border: none;
}
.btn-primary-modern:hover,
.btn-primary-modern:focus,
.btn-primary-modern:active,
.btn-primary-modern {
    background-color: #007bff; /* Bootstrap primary */
    color: white;
    border: none;
}
.btn-success-modern:hover,
.btn-success-modern:focus,
.btn-success-modern:active,
.btn-success-modern {
    background-color: var(--primary-green); /* Ensure --primary-green is defined */
    color: white;
    border: none;
}


    /* DataTable Styling */
    .dataTables_wrapper {
        font-size: 14px;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_processing,
    .dataTables_wrapper .dataTables_paginate {
        color: #495057;
        margin-bottom: 16px;
    }

    .dataTables_wrapper .dataTables_filter input {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 8px 12px;
        margin-left: 8px;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--primary-green);
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(0, 165, 81, 0.15);
    }

    .dataTables_wrapper .dataTables_length select {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 6px 10px;
        margin: 0 8px;
    }


    /* Export Buttons */
    .dt-buttons {
        margin-bottom: 20px;
    }

    .dt-button {
        border-radius: 8px !important;
        font-weight: 600 !important;
        font-size: 14px !important;
        padding: 10px 16px !important;
        margin-right: 8px !important;
        border: none !important;
        transition: all 0.3s ease !important;
    }



.buttons-excel {
    background-color: #28a745 !important; /* Bootstrap success green */
    color: white !important;
    border: none !important;
}

.buttons-pdf {
    background-color: #dc3545 !important; /* Bootstrap danger red */
    color: white !important;
    border: none !important;
}

.buttons-print {
    background-color: #007bff !important; /* Bootstrap primary blue */
    color: white !important;
    border: none !important;
}


    /* Animation */
    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .btn-modern {
            padding: 6px 10px;
            font-size: 12px;
        }

        table.dataTable tbody td {
            padding: 8px;
            font-size: 13px;
        }

        .dt-button {
            width: 100% !important;
            margin-bottom: 8px !important;
            margin-right: 0 !important;
        }

        
    }
</style>
@endpush

<div class="page-header">
    <h1 class="page-title">Receipt Vouchers</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Receipt List</li>
        </ol>
    </nav>
</div>

<!-- Statistics Row -->
@php
    $totalReceipts = $receipts->count();
    $cashTotal = $receipts->where('payment', 'CASH')->sum('dr_total');
    $chequeTotal = $receipts->where('payment', 'CHEQUE')->sum('dr_total');
    $onlineTotal = $receipts->where('payment', 'ONLINE')->sum('dr_total');
@endphp



<!-- Main Data Table Card -->
<div class="card fade-in">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            Receipt List
        </h6>
    
        <a href="{{ route('accounts.receipt.add') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Receipt</a>
    </div>
    <div class="card-body">
    

        <div class="table-responsive">
            <table class="table table-hover" id="receiptTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Receipt No</th>
                        <th>Received From</th>
                        <th>Payment Mode</th>
                        <th>Amount</th>
                        <th>Narration</th>
                        <th>Actions</th>
                        <th style="display: none;">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receipts as $receipt)
                    <tr>
                        <td>{{ date('d-m-Y', strtotime($receipt->date)) }}</td>
                        <td>{{ $receipt->entry_code }}</td>
                        <td>{{ $receipt->paid_to }}</td>
                        <td>
                            <span class="status-badge status-{{ strtolower($receipt->payment) }}">
                                {{ $receipt->payment }}
                            </span>
                            @if($receipt->payment == 'CHEQUE' && $receipt->cheque_no)
                                <br><small class="text-muted">{{ $receipt->cheque_no }}</small>
                            @endif
                        </td>
                        <td>RM {{ number_format($receipt->dr_total, 2) }}</td>
                        <td>{{ Str::limit($receipt->narration, 50) }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('accounts.receipt.view', $receipt->id) }}" 
                                   class="btn btn-modern btn-info-modern" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('accounts.receipt.print', $receipt->id) }}" 
                                   class="btn btn-modern btn-warning-modern" title="Print" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                                @if(empty($receipt->inv_type))
                                    <a href="{{ route('accounts.receipt.edit', $receipt->id) }}" 
                                       class="btn btn-modern btn-primary-modern" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('accounts.receipt.copy', $receipt->id) }}" 
                                       class="btn btn-modern btn-success-modern" title="Copy">
                                        <i class="fas fa-copy"></i>
                                    </a>
                                @endif
                            </div>
                        </td>

                        
                        <td style="display: none;">{{ @$receipt->updated_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable with export buttons
    $('#receiptTable').DataTable({
    autoWidth: false,
        paging: true, // Explicitly enable pagination
        pageLength: 25, // Number of rows per page
        order: [[7, "desc"]],
        dom: 'Brtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel me-2"></i>Excel',
                className: 'buttons-excel',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Exclude actions column
                },
                messageTop: function() {
                    return 'RSK Canvas Trading\nNo. 8 Lot 2921, Jalan PJS 3/1, Taman Medan, 46000 Petaling Jaya,\nSelangor Darul Ehsan.\nTel: +603-7781 7434 / +603-7785 7434\nE-mail: sales@rsk.com.my';
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf me-2"></i>PDF',
                className: 'buttons-pdf',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Exclude actions column
                },
                customize: function(doc) {
                    // Add company header
                    doc.content.splice(0, 0, {
                        margin: [0, 0, 20, 10],
                        columns: [
                            {
                                image: 'data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path("assets/images/grasp_logo.png"))) }}',
                                width: 90,
                                margin: [0, 0, 11, 0]
                            },
                            {
                                stack: [
                                    {
                                        text: 'RSK Canvas Trading',
                                        fontSize: 10,
                                        bold: true,
                                        color: '#e16c2f',
                                    },
                                    {
                                        text: 'No. 8 Lot 2921, Jalan PJS 3/1, Taman Medan, 46000 Petaling Jaya, Selangor Darul Ehsan.',
                                        fontSize: 9,
                                    },
                                    {
                                        text: 'Tel : +603-7781 7434 / +603-7785 7434',
                                        fontSize: 9
                                    },
                                    {
                                        text: 'E-mail : sales@rsk.com.my',
                                        fontSize: 9
                                    }
                                ],
                                margin: [10, 10, 0, 0]
                            }
                        ]
                    });

                    // Style the table
                    let tableIndex = -1;
                    for (let i = 0; i < doc.content.length; i++) {
                        if (doc.content[i].table) {
                            tableIndex = i;
                            break;
                        }
                    }

                    if (tableIndex !== -1 && doc.content[tableIndex] && doc.content[tableIndex].table) {
                        const table = doc.content[tableIndex].table;
                        table.widths = ['12%', '18%', '20%', '15%', '15%', '20%'];
                        doc.content[tableIndex].alignment = 'left';

                        const body = table.body;

                        // Apply formatting
                        for (let i = 0; i < body.length; i++) {
                            for (let j = 0; j < body[i].length; j++) {
                                if (i === 0) {
                                    // Header row
                                    if (typeof body[i][j] === 'string') {
                                        body[i][j] = {
                                            text: body[i][j],
                                            fontSize: 10,
                                            bold: true,
                                            alignment: 'left',
                                            margin: [3, 3, 3, 3],
                                            fillColor: '#2d4154',
                                            color: 'white'
                                        };
                                    }
                                } else {
                                    // Body rows
                                    if (typeof body[i][j] === 'string') {
                                        body[i][j] = {
                                            text: body[i][j],
                                            fontSize: 8,
                                            margin: [2, 2, 2, 2],
                                            fillColor: i % 2 === 0 ? '#f8f9fa' : '#ffffff'
                                        };
                                    }
                                }
                            }
                        }

                        // Remove borders
                        doc.content[tableIndex].layout = {
                            hLineWidth: function(i, node) { return 0; },
                            vLineWidth: function(i, node) { return 0; },
                            paddingLeft: function(i, node) { return 4; },
                            paddingRight: function(i, node) { return 4; },
                            paddingTop: function(i, node) { return 2; },
                            paddingBottom: function(i, node) { return 2; }
                        };
                    }

                    doc.defaultStyle = { fontSize: 8 };
                    doc.styles.tableHeader = {
                        fontSize: 10,
                        bold: true,
                        alignment: 'left',
                        fillColor: '#2d4154',
                        color: 'white'
                    };
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print me-2"></i>Print',
                className: 'buttons-print',
                title: 'RSK CANVAS TRADING',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Exclude actions column
                },
                customize: function(win) {
                    $(win.document.head).append(`
                        <style>
                            h1 {
                                font-size: 30px !important;
                                text-align: center;
                            }
                        </style>
                    `);
                    $(win.document.body).prepend(`
                        <div style="display: flex; align-items: flex-start;">
                            <div style="margin-top: 5px;">
                                <img src="{{ asset('assets/images/grasp_logo.png') }}" width="100" style="margin-right: 15px;">
                            </div>
                            <div style="font-size: 12px;">
                                <strong style="font-size: 20px; color:#e16c2f;">RSK Canvas Trading</strong><br>
                                No. 8 Lot 2921, Jalan PJS 3/1, Taman Medan, 46000 Petaling Jaya,
                                Selangor Darul Ehsan.<br>
                                Tel : +603-7781 7434 / +603-7785 7434<br>
                                E-mail : sales@rsk.com.my
                            </div>
                        </div>
                    `);
                }
            }
        ],
 
    });

    // Add hover effects to action buttons
    $('.btn-modern').hover(
        function() {
            $(this).addClass('shadow-lg');
        },
        function() {
            $(this).removeClass('shadow-lg');
        }
    );
});
</script>
@endpush

@endsection