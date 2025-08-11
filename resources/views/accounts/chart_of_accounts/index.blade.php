@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h5 class="page-title">Chart of Accounts</h5>
        <div class="ms-panel">
            <div class="ms-panel-header">
                <div class="d-flex justify-content-between">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb pl-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="material-icons"></i> Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Chart of Accounts</li>
                        </ol>
                    </nav>
                    <div>
                        @if($activeYear)
                        <span class="badge badge-primary">Active Year: {{ $activeYear->getPeriodStringAttribute() }}</span>
                        @endif

                        @if($permissions['can_view_details'])
                        <a href="{{ route('funds.index') }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-list"></i> Funds
                        </a>
                        @endif

                        @if($permissions['can_create_group'])
                        <a href="{{ route('chart_of_accounts.group.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Add Group
                        </a>
                        @endif

                        @if($permissions['can_create_ledger'])
                        <a href="{{ route('chart_of_accounts.ledger.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Ledger
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            <br>
            <div class="ms-panel-body">
                <div class="row">
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Account Structure</h6>
                            </div>
                            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                <div id="account-tree"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0">Details</h6>
                            </div>
                            <div class="card-body">
                                <div id="details-panel">
                                    <p class="text-center text-muted">Select a group or ledger from the tree to view details</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Assets</h5>
                                <h4 id="total-assets">RM 0.00</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5 class="card-title" style="font-size: 17px; padding-top: 5px;">Total Liabilities & Equity</h5>
                                <h4 id="total-liabilities">RM 0.00</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Income</h5>
                                <h4 id="total-income">RM 0.00</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Expenses</h5>
                                <h4 id="total-expenses">RM 0.00</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="deleteMessage">Are you sure you want to delete this?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<style>
    .badge.badge-warning {
        background-color: #fda600;
    }

    .badge.badge-primary {
        background-color: #357ffa;
    }
    .badge.badge-success {
    background-color: #07be6e;
}
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let selectedId = null;
    let selectedType = null;
    
    // Initialize tree
    $('#account-tree').jstree({
        'core': {
            'data': {
                'url': '{{ route("chart_of_accounts.tree_data") }}',
                'dataType': 'json'
            },
            'themes': {
                'dots': true,
                'icons': true
            }
        },
        'types': {
            'group': {
                'icon': 'fas fa-folder'
            },
            'ledger': {
                'icon': 'fas fa-file-alt'
            }
        },
        'plugins': ['types', 'search']
    });
    
    // Handle node selection
    $('#account-tree').on('select_node.jstree', function(e, data) {
        if (data.node.type === 'ledger') {
            selectedType = 'ledger';
            selectedId = data.node.data.id;
            loadLedgerDetails(selectedId);
        } else if (data.node.type === 'group') {
            selectedType = 'group';
            selectedId = data.node.data.id;
            loadGroupDetails(selectedId);
        }
    });
    
    // Load group details
    function loadGroupDetails(groupId) {
        $.ajax({
            url: '{{ url("group") }}/' + groupId + '/details',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    let group = response.group;
                    let isFixed = group.fixed == 1;
                    
                    let html = `
                        <h5>Group Details</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Group Name:</th>
                                <td>${group.name}</td>
                            </tr>
                            <tr>
                                <th>Group Code:</th>
                                <td>${group.code}</td>
                            </tr>
                            <tr>
                                <th>Parent Group:</th>
                                <td>${group.parent ? group.parent.name + ' (' + group.parent.code + ')' : 'None (Top Level)'}</td>
                            </tr>
                            <tr>
                                <th>Type:</th>
                                <td>${isFixed ? '<span class="badge badge-warning">System Group</span>' : '<span class="badge badge-success">User Created</span>'}</td>
                            </tr>
                            <tr>
                                <th>Sub-groups:</th>
                                <td>${group.children.length}</td>
                            </tr>
                            <tr>
                                <th>Ledgers:</th>
                                <td>${group.ledgers.length}</td>
                            </tr>
                        </table>
                        <div class="mt-3">
                            ${!isFixed ? `
                                <a href="{{ url('group') }}/${group.id}/edit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button class="btn btn-danger btn-sm" onclick="deleteGroup(${group.id})">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            ` : '<span class="text-muted">System groups cannot be modified</span>'}
                        </div>
                    `;
                    
                    $('#details-panel').html(html);
                }
            }
        });
    }
    
    // Load ledger details
    function loadLedgerDetails(ledgerId) {
        $.ajax({
            url: '{{ url("chart-of-accounts/ledger") }}/' + ledgerId + '/details',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    let ledger = response.ledger;
                    let balance = ledger.opening_balance ? ledger.opening_balance[0] : null;
                    
                    let html = `
                        <h5>Ledger Details</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Ledger Name:</th>
                                <td>${ledger.name}</td>
                            </tr>
                            <tr>
                                <th>Ledger Code:</th>
                                <td>${ledger.left_code || ''} / ${ledger.right_code || ''}</td>
                            </tr>
                            <tr>
                                <th>Group:</th>
                                <td>${ledger.group.name} (${ledger.group.code})</td>
                            </tr>
                            <tr>
                                <th>Opening Balance:</th>
                                <td>${balance ? 'RM ' + Number(balance.dr_amount - balance.cr_amount).toFixed(2) : 'RM 0.00'}</td>
                            </tr>
                            <tr>
                                <th>Features:</th>
                                <td>
                                    ${ledger.type == 1 ? '<span class="badge badge-info">Bank/Cash</span>' : ''}
                                    ${ledger.reconciliation ? '<span class="badge badge-success">Reconciliation</span>' : ''}
                                    ${ledger.pa ? '<span class="badge badge-warning">P&L Accumulation</span>' : ''}
                                    ${ledger.aging ? '<span class="badge badge-primary">Aging</span>' : ''}
                                    ${ledger.credit_aging ? '<span class="badge badge-danger">Credit Aging</span>' : ''}
                                    ${ledger.iv ? '<span class="badge badge-secondary">Inventory</span>' : ''}
                                </td>
                            </tr>
                            ${ledger.notes ? `<tr><th>Notes:</th><td>${ledger.notes}</td></tr>` : ''}
                        </table>
                        <div class="mt-3">
                            <a href="{{ url('chart-of-accounts/ledger') }}/${ledger.id}/edit" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button class="btn btn-danger btn-sm" onclick="deleteLedger(${ledger.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    `;
                    
                    $('#details-panel').html(html);
                }
            }
        });
    }
    
    // Delete group
    window.deleteGroup = function(groupId) {
        selectedId = groupId;
        selectedType = 'group';
        $('#deleteMessage').text('Are you sure you want to delete this group?');
        $('#deleteModal').modal('show');
    }
    
    // Delete ledger
    window.deleteLedger = function(ledgerId) {
        selectedId = ledgerId;
        selectedType = 'ledger';
        $('#deleteMessage').text('Are you sure you want to delete this ledger?');
        $('#deleteModal').modal('show');
    }
           const successMessage = localStorage.getItem('delete_success');
        if (successMessage) {
            $('#alert-container').html(`
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    ${successMessage}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `);
            localStorage.removeItem('delete_success'); // Clear after showing
        }
    $('#confirmDelete').click(function() {
    if (selectedId && selectedType) {
        let url = selectedType === 'group' 
            ? '{{ url("chart-of-accounts/group") }}/' + selectedId
            : '{{ url("chart-of-accounts/ledger") }}/' + selectedId;
            
        $.ajax({
            url: url,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteModal').modal('hide');
                    localStorage.setItem('delete_success', response.message);
                    
                    // Refresh the tree and totals
                    $('#account-tree').jstree(true).refresh();
                    loadSummary(); // Add this line
                    
                    // Clear details panel
                    $('#details-panel').html('<p class="text-center text-muted">Select a group or ledger from the tree to view details</p>');
                } else {
                    $('#deleteModal').modal('hide');
                    alert(response.message);
                }
            },
            error: function() {
                $('#deleteModal').modal('hide');
                alert('An error occurred while deleting.');
            }
        });
    }
});
    
    // Load summary data
    loadSummary();
    
   function loadSummary() {
        // Show loading state
        $('#total-assets').html('<i class="fas fa-spinner fa-spin"></i>');
        $('#total-liabilities').html('<i class="fas fa-spinner fa-spin"></i>');
        $('#total-income').html('<i class="fas fa-spinner fa-spin"></i>');
        $('#total-expenses').html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: '{{ route("chart_of_accounts.summary_totals") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#total-assets').text('RM ' + response.totals.assets);
                    $('#total-liabilities').text('RM ' + response.totals.liabilities);
                    $('#total-income').text('RM ' + response.totals.income);
                    $('#total-expenses').text('RM ' + response.totals.expenses);
                } else {
                    // Show error state
                    $('.card h3').text('Error loading');
                    console.error('Error loading summary:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error loading summary:', error);
                $('#total-assets').text('Error');
                $('#total-liabilities').text('Error');
                $('#total-income').text('Error');
                $('#total-expenses').text('Error');
            }
        });
    }
    
});
</script>
@endsection
