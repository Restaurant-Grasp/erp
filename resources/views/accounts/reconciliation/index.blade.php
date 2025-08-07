@extends('layouts.app')
@section('title', 'Bank Reconciliation')
@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">Bank Reconciliation List</h5>
     
        </div>
        @php
        $role = auth()->user()->getRoleNames()->first();
        $permissions = getCurrentRolePermissions($role);
        @endphp
        @if ($permissions->contains('name', 'accounts.reconciliation.create'))
        <a href="{{ route('accounts.reconciliation.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>New Reconciliation
        </a>
        @endif
    </div>
    
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

     

        <div class="table-responsive">
            <table class="table table-hover" id="reconciliationTable">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Bank Account</th>
                        <th class="text-end">Statement Balance </th>
                        <th class="text-end">Reconciled Balance </th>
                        <th class="text-end">Difference </th>
                        <th class="text-center">Status</th>
                        <th>Reconciled By</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reconciliations as $reconciliation)
                    <tr>
                        <td>
                            <span class="fw-medium">{{ $reconciliation->month_display }}</span>
                        </td>
                        <td>
                            <strong>{{ $reconciliation->ledger->name }}</strong>
                        </td>
                        <td class="text-end">
                            <span class="fw-medium">{{ number_format($reconciliation->statement_closing_balance, 2) }}</span>
                        </td>
                        <td class="text-end">
                            <span class="fw-medium">{{ number_format($reconciliation->reconciled_balance, 2) }}</span>
                        </td>
                        <td class="text-end">
                            @if(abs($reconciliation->difference) > 0.01)
                                <span class="text-danger fw-bold">{{ number_format($reconciliation->difference, 2) }}</span>
                            @else
                                <span class="text-success fw-bold">0.00</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $statusConfig = [
                                    'draft' => ['bg-warning', 'fas fa-edit', 'Draft'],
                                    'completed' => ['bg-success', 'fas fa-check', 'Completed'],
                                    'locked' => ['bg-secondary', 'fas fa-lock', 'Locked']
                                ];
                                $config = $statusConfig[$reconciliation->status] ?? ['bg-secondary', 'fas fa-question', 'Unknown'];
                            @endphp
                            <span class="badge {{ $config[0] }}">
                                <i class="{{ $config[1] }} me-1"></i>{{ $config[2] }}
                            </span>
                        </td>
                        <td>
                            @if($reconciliation->reconciledBy)
                                <i class="fas fa-user me-1 text-muted"></i>{{ $reconciliation->reconciledBy->name }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($reconciliation->reconciled_date)
                                {{ $reconciliation->reconciled_date->format('d M Y') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                @if($reconciliation->status == 'draft')
                                    @if ($permissions->contains('name', 'accounts.reconciliation.process'))
                                    <a href="{{ route('accounts.reconciliation.process', $reconciliation->id) }}"
                                       class="btn btn-sm btn-outline-primary" title="Continue Reconciliation">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                @else
                                    @if ($permissions->contains('name', 'accounts.reconciliation.view'))
                                    <a href="{{ route('accounts.reconciliation.view', $reconciliation->id) }}"
                                       class="btn btn-sm btn-outline-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endif
                                @endif

                                <a href="{{ route('accounts.reconciliation.report', $reconciliation->id) }}"
                                   class="btn btn-sm btn-outline-success" title="Generate Report" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                @if($reconciliation->status == 'completed' && $permissions->contains('name', 'accounts.reconciliation.lock'))
                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                        onclick="lockReconciliation({{ $reconciliation->id }})" title="Lock Reconciliation">
                                    <i class="fas fa-lock"></i>
                                </button>
                                @endif

                                @if($reconciliation->status != 'locked' && $permissions->contains('name', 'accounts.reconciliation.delete'))
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteReconciliation({{ $reconciliation->id }})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-balance-scale fa-3x mb-3"></i>
                                <p class="mb-0">No reconciliations found</p>
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
    var table = $('#reconciliationTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel me-1"></i>Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                },
                title: 'Bank Reconciliation Report',
                messageTop: 'RSK Canvas Trading\nNo. 8 Lot 2921, Jalan PJS 3/1, Taman Medan, 46000 Petaling Jaya,\nSelangor Darul Ehsan.\nTel: +603-7781 7434 / +603-7785 7434\nE-mail: sales@rsk.com.my'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf me-1"></i>PDF',
                className: 'btn btn-danger btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                },
                customize: function (doc) {
                    doc.content.splice(0, 0, {
                        text: 'Bank Reconciliation Report',
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
                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                }
            }
        ],
        order: [[0, "desc"]],
        pageLength: 25,
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search reconciliations..."
        }
    });

    // Custom filters
    $('#filterBtn').click(function() {
        const month = $('#monthFilter').val();
        const status = $('#statusFilter').val();
        const bank = $('#bankFilter').val();

        // Apply filters
        table.columns(0).search(month || '');
        table.columns(5).search(status || '');
        table.columns(1).search(bank || '');
        table.draw();
    });

    $('#clearBtn').click(function() {
        $('#monthFilter, #statusFilter, #bankFilter').val('');
        table.search('').columns().search('').draw();
    });
});

function lockReconciliation(id) {
    if (confirm('Are you sure you want to lock this reconciliation? This action cannot be undone.')) {
        fetch(`/accounts/reconciliation/${id}/lock`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error locking reconciliation');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error locking reconciliation');
        });
    }
}

function deleteReconciliation(id) {
    if (confirm('Are you sure you want to delete this reconciliation? This will unmark all reconciled transactions.')) {
        fetch(`/accounts/reconciliation/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting reconciliation');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting reconciliation');
        });
    }
}
</script>
@endpush
@endsection