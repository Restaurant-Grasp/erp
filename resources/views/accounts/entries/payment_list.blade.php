@extends('layouts.app')
@section('title', 'Payment Vouchers')
@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">Payment Vouchers</h5>
        </div>
        @php
        $role = auth()->user()->getRoleNames()->first();
        $permissions = getCurrentRolePermissions($role);
        @endphp
        @if ($permissions->contains('name', 'accounts.payment.create'))
        <a href="{{ route('accounts.payment.add') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add Payment
        </a>
        @endif
    </div>
    
    <div class="card-body">
    <div class="table-responsive">
            <table class="table table-hover" id="paymentTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Payment No</th>
                        <th>Paid To</th>
                        <th>Payment Mode</th>
                        <th>Amount (RM)</th>
                        <th>Narration</th>
                        <th class="text-center">Actions</th>
                        <th style="display: none;">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td>
                            <span class="fw-medium">{{ $payment->date->format('d M Y') }}</span>
                        </td>
                        <td>
                            <code class="text-primary">{{ $payment->entry_code }}</code>
                        </td>
                        <td>{{ $payment->paid_to }}</td>
                        <td>
                            @php
                                $badgeClass = match($payment->payment) {
                                    'CASH' => 'bg-success',
                                    'CHEQUE' => 'bg-warning text-dark',
                                    'ONLINE' => 'bg-info',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $payment->payment }}</span>
                            @if($payment->payment == 'CHEQUE' && $payment->cheque_no)
                                <br><small class="text-muted">{{ $payment->cheque_no }}</small>
                            @endif
                        </td>
                        <td class="text-end">
                            <strong>{{ number_format($payment->cr_total, 2) }}</strong>
                        </td>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                  title="{{ $payment->narration }}">
                                {{ $payment->narration }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                @if ($permissions->contains('name', 'accounts.payment.view'))
                                <a href="{{ route('accounts.payment.view', $payment->id) }}" 
                                   class="btn btn-sm btn-outline-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endif
                                
                                <a href="{{ route('accounts.payment.print', $payment->id) }}" 
                                   class="btn btn-sm btn-outline-warning" title="Print" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                                
                                @if(empty($payment->inv_type))
                                    @if ($permissions->contains('name', 'accounts.payment.edit'))
                                    <a href="{{ route('accounts.payment.edit', $payment->id) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                    
                                    @if ($permissions->contains('name', 'accounts.payment.create'))
                                    <a href="{{ route('accounts.payment.copy', $payment->id) }}" 
                                       class="btn btn-sm btn-outline-success" title="Copy">
                                        <i class="fas fa-copy"></i>
                                    </a>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td style="display: none;">{{ $payment->updated_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-receipt fa-3x mb-3"></i>
                                <p class="mb-0">No payment vouchers found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#paymentTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel me-1"></i>Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf me-1"></i>PDF',
                className: 'btn btn-danger btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                },
                customize: function (doc) {
                    doc.content.splice(0, 0, {
                        text: 'Payment Vouchers Report',
                        fontSize: 18,
                        alignment: 'center',
                        margin: [0, 0, 0, 20]
                    });
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print me-1"></i>Print',
                className: 'btn btn-primary btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                }
            }
        ],
        order: [[7, "desc"]],
        pageLength: 25,
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search payments..."
        }
    });

    // Calculate totals by payment mode
    function calculateTotals() {
        let cashTotal = 0, chequeTotal = 0, onlineTotal = 0;
        
        table.rows({ search: 'applied' }).data().each(function(row, index) {
            const amount = parseFloat(row[4].replace(/[^\d.-]/g, ''));
            const mode = $(row[3]).find('.badge').text().trim();
            
            switch(mode) {
                case 'CASH':
                    cashTotal += amount;
                    break;
                case 'CHEQUE':
                    chequeTotal += amount;
                    break;
                case 'ONLINE':
                    onlineTotal += amount;
                    break;
            }
        });
        
        $('#cashTotal').text('RM ' + cashTotal.toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#chequeTotal').text('RM ' + chequeTotal.toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#onlineTotal').text('RM ' + onlineTotal.toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#grandTotal').text('RM ' + (cashTotal + chequeTotal + onlineTotal).toLocaleString('en-US', {minimumFractionDigits: 2}));
    }

    // Initial calculation
    calculateTotals();

    // Recalculate on search/filter
    table.on('draw', function() {
        calculateTotals();
    });

    // Custom filters
    $('#filterBtn').click(function() {
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        const paymentMode = $('#paymentMode').val();

        // Apply filters (this is a simplified version - you'd need server-side filtering for production)
        table.draw();
    });

    $('#clearBtn').click(function() {
        $('#dateFrom, #dateTo, #paymentMode').val('');
        table.search('').draw();
    });
});
</script>
@endpush
@endsection