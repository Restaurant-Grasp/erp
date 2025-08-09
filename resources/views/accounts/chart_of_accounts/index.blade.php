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


@endsection
