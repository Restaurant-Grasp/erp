@extends('layouts.app')
@section('title', 'Fund Management')

@section('content')
<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb pl-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Fund Management</li>
            </ol>
        </nav>

        <br>
        <!-- Main Fund Management Card -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        Fund Management
                    </h5>
                    <div class="d-flex gap-2">


                        <!-- Add New Fund Button -->
                        <a href="{{ route('funds.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Fund
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">

                <!-- Funds Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="fundsTable">
                        <thead>
                            <tr>
                                <th width="50">#</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($funds as $key => $fund)
                            <tr data-fund-id="{{ $fund->id }}">
                                <td>{{ $key + 1 }}</td>

                                <!-- Fund Details -->
                                <td>
                                    {{ $fund->code }}
                                </td>

                                <!-- Status -->
                                <td>
                                    {{ $fund->name }}
                                </td>

                                <!-- Financial Data -->
                                <td>
                                    {{ $fund->description }}
                                </td>
                                <td>
                                    {{ $fund->created_at ? $fund->created_at->format('d-m-Y') : '' }}
                                </td>

                                <!-- Actions -->
                                <td>
                                    <div class="btn-group" role="group">
                                       

                                        <!-- Edit -->
                                        <a href="{{ route('funds.edit', $fund->id) }}"
                                            class="btn btn-sm btn-outline-success"
                                            title="Edit Fund">
                                            <i class="fas fa-edit"></i>
                                        </a>


                                        <form action="{{ route('funds.destroy', $fund->id) }}"
                                            method="POST"
                                            class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this fund?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>


                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted">

                                        <h5>No funds found</h5>

                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>


                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div>
                    <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                    <h5>Are you sure?</h5>
                    <p>You are about to delete the fund "<strong id="fundName"></strong>".</p>
                    <p class="text-muted">This action cannot be undone.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="fas fa-trash me-2"></i>Delete Fund
                </button>
            </div>
        </div>
    </div>
</div>


</form>

<style>
    .avatar-sm {
        width: 40px;
        height: 40px;
    }

    .table th {
        border-top: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .progress {
        background-color: rgba(0, 0, 0, 0.1);
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    @keyframes pulse {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }

        100% {
            opacity: 1;
        }
    }

    .loading {
        animation: pulse 1.5s infinite;
    }

    .card:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
</style>

@endsection