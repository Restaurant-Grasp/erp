@extends('layouts.app')
@section('title', 'Journal Entries')
@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">Journal Entries</h5>
          
            
        </div>
        @php
        $role = auth()->user()->getRoleNames()->first();
        $permissions = getCurrentRolePermissions($role);
        @endphp
        @if ($permissions->contains('name', 'accounts.journal.create'))
        <a href="{{ route('accounts.journal.add') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add Journal Entry
        </a>
        @endif
    </div>
    
    <div class="card-body">
       
    
    

        <div class="table-responsive">
            <table class="table table-hover" id="journalTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Journal No</th>
                        <th class="text-end">Debit Total</th>
                        <th class="text-end">Credit Total</th>
                        <th>Narration</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                        <th style="display: none;">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journals as $journal)
                    <tr>
                        <td>
                            <span class="fw-medium">{{ $journal->date->format('d M Y') }}</span>
                        </td>
                        <td>
                            <code class="text-primary">{{ $journal->entry_code }}</code>
                        </td>
                        <td class="text-end">
                            <strong>{{ number_format($journal->dr_total, 2) }}</strong>
                        </td>
                        <td class="text-end">
                            <strong>{{ number_format($journal->cr_total, 2) }}</strong>
                        </td>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                  title="{{ $journal->narration }}">
                                {{ $journal->narration ?: 'No description' }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($journal->isBalanced())
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>Balanced
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Unbalanced
                                </span>
                                <br><small class="text-muted">
                                    Diff: {{ number_format(abs($journal->dr_total - $journal->cr_total), 2) }}
                                </small>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                @if ($permissions->contains('name', 'accounts.journal.view'))
                                <a href="{{ route('accounts.journal.view', $journal->id) }}" 
                                   class="btn btn-sm btn-outline-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endif
                                
                                <a href="{{ route('accounts.journal.print', $journal->id) }}" 
                                   class="btn btn-sm btn-outline-warning" title="Print" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                                
                                @if(empty($journal->inv_type))
                                    @if ($permissions->contains('name', 'accounts.journal.edit'))
                                    <a href="{{ route('accounts.journal.edit', $journal->id) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                    
                                    @if ($permissions->contains('name', 'accounts.journal.create'))
                                    <a href="{{ route('accounts.journal.copy', $journal->id) }}" 
                                       class="btn btn-sm btn-outline-success" title="Copy">
                                        <i class="fas fa-copy"></i>
                                    </a>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td style="display: none;">{{ $journal->updated_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-book fa-3x mb-3"></i>
                                <p class="mb-0">No journal entries found</p>
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
    var table = $('#journalTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel me-1"></i>Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                },
                title: 'Journal Entries Report',
                messageTop: 'RSK Canvas Trading\nNo. 8 Lot 2921, Jalan PJS 3/1, Taman Medan, 46000 Petaling Jaya,\nSelangor Darul Ehsan.\nTel: +603-7781 7434 / +603-7785 7434\nE-mail: sales@rsk.com.my'
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
                        text: 'Journal Entries Report',
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
                },
                customize: function (win) {
                    $(win.document.body).prepend(`
                        <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
                            <div style="font-size: 12px;">
                                <strong style="font-size: 20px; color:#e16c2f;">RSK Canvas Trading</strong><br>
                                No. 8 Lot 2921, Jalan PJS 3/1, Taman Medan, 46000 Petaling Jaya,<br>
                                Selangor Darul Ehsan.<br>
                                Tel: +603-7781 7434 / +603-7785 7434<br>
                                E-mail: sales@rsk.com.my
                            </div>
                        </div>
                    `);
                }
            }
        ],
        order: [[7, "desc"]],
        pageLength: 25,
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search journal entries..."
        }
    });

    // Calculate summary statistics
    function calculateSummaries() {
        let balancedCount = 0, unbalancedCount = 0;
        let totalDebits = 0, totalCredits = 0;
        
        table.rows({ search: 'applied' }).data().each(function(row, index) {
            const debitAmount = parseFloat(row[2].replace(/[^\d.-]/g, ''));
            const creditAmount = parseFloat(row[3].replace(/[^\d.-]/g, ''));
            const statusCell = $(row[5]);
            
            if (statusCell.find('.bg-success').length) {
                balancedCount++;
            } else {
                unbalancedCount++;
            }
            
            totalDebits += debitAmount;
            totalCredits += creditAmount;
        });
        
        $('#balancedCount').text(balancedCount);
        $('#unbalancedCount').text(unbalancedCount);
        $('#totalDebits').text('RM ' + totalDebits.toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#totalCredits').text('RM ' + totalCredits.toLocaleString('en-US', {minimumFractionDigits: 2}));
    }

    // Initial calculation
    calculateSummaries();

    // Recalculate on search/filter
    table.on('draw', function() {
        calculateSummaries();
    });

    // Custom filters
    $('#filterBtn').click(function() {
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        const status = $('#statusFilter').val();

        // Simple client-side filtering (for production, use server-side filtering)
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            const rowDate = new Date(data[0]);
            const filterFromDate = dateFrom ? new Date(dateFrom) : null;
            const filterToDate = dateTo ? new Date(dateTo) : null;
            const statusMatch = !status || 
                (status === 'balanced' && $(data[5]).find('.bg-success').length > 0) ||
                (status === 'unbalanced' && $(data[5]).find('.bg-danger').length > 0);

            const dateMatch = (!filterFromDate || rowDate >= filterFromDate) &&
                             (!filterToDate || rowDate <= filterToDate);

            return dateMatch && statusMatch;
        });

        table.draw();
    });

    $('#clearBtn').click(function() {
        $('#dateFrom, #dateTo, #statusFilter').val('');
        $.fn.dataTable.ext.search.pop();
        table.draw();
    });
});
</script>
@endpush
@endsection