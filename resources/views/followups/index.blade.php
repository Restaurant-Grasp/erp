@extends('layouts.app')

@section('title', 'Follow-up Dashboard')

@section('content')
<div class="page-header">
    <h1 class="page-title">Follow-up Dashboard</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Follow-ups</li>
        </ol>
    </nav>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-calendar-check fa-2x text-primary mb-2"></i>
                <h5 class="card-title">Total Active</h5>
                <h3>{{ $stats['total'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-exclamation-circle fa-2x text-danger mb-2"></i>
                <h5 class="card-title">Overdue</h5>
                <h3 class="text-danger">{{ $stats['overdue'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-calendar-day fa-2x text-warning mb-2"></i>
                <h5 class="card-title">Today</h5>
                <h3 class="text-warning">{{ $stats['today'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h5 class="card-title">Completed This Month</h5>
                <h3 class="text-success">{{ $stats['completed'] }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('followups.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="type">
                        <option value="">All Types</option>
                        <option value="phone_call" {{ request('type') == 'phone_call' ? 'selected' : '' }}>Phone Call</option>
                        <option value="email" {{ request('type') == 'email' ? 'selected' : '' }}>Email</option>
                        <option value="whatsapp" {{ request('type') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                        <option value="in_person_meeting" {{ request('type') == 'in_person_meeting' ? 'selected' : '' }}>In-Person Meeting</option>
                        <option value="video_call" {{ request('type') == 'video_call' ? 'selected' : '' }}>Video Call</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="priority">
                        <option value="">All Priorities</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>
                <div class="col-md-2">
                    @if(auth()->user()->hasRole(['super_admin', 'admin']))
                    <select class="form-select" name="assigned_to">
                        <option value="">All Staff</option>
                        @foreach($staff as $member)
                        <option value="{{ $member->id }}" {{ request('assigned_to') == $member->id ? 'selected' : '' }}>
                            {{ $member->name }}
                        </option>
                        @endforeach
                    </select>
                    @endif
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}" 
                           placeholder="From Date">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}" 
                           placeholder="To Date">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('followups.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Clear
                    </a>
                    <div class="float-end">
                        <a href="{{ route('followups.calendar') }}" class="btn btn-info">
                            <i class="fas fa-calendar me-1"></i> Calendar View
                        </a>
                        @can('followups.create')
                        <a href="{{ route('followups.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> New Follow-up
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Follow-ups List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Follow-ups</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="50">Type</th>
                        <th>Subject</th>
                        <th>Entity</th>
                        <th width="120">Scheduled</th>
                        <th width="80">Priority</th>
                        <th width="100">Status</th>
                        <th width="120">Assigned To</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($followUps as $followUp)
                    <tr class="{{ $followUp->status === 'overdue' ? 'table-danger' : '' }}">
                        <td class="text-center">
                            <i class="fas {{ $followUp->type_icon }} fa-lg text-{{ $followUp->follow_up_type === 'whatsapp' ? 'success' : 'primary' }}"
                               title="{{ ucwords(str_replace('_', ' ', $followUp->follow_up_type)) }}"></i>
                        </td>
                        <td>
                            <a href="{{ route('followups.show', $followUp) }}" class="text-decoration-none">
                                <strong>{{ $followUp->subject }}</strong>
                            </a>
                            @if($followUp->is_recurring)
                            <span class="badge bg-info ms-1" title="Recurring">
                                <i class="fas fa-redo"></i>
                            </span>
                            @endif
                        </td>
                        <td>
                            @if($followUp->lead)
                                <span class="badge bg-primary">Lead</span>
                                <a href="{{ route('leads.show', $followUp->lead) }}">
                                    {{ $followUp->entity_name }}
                                </a>
                            @elseif($followUp->customer)
                                <span class="badge bg-success">Customer</span>
                                <a href="{{ route('customers.show', $followUp->customer) }}">
                                    {{ $followUp->entity_name }}
                                </a>
                            @endif
                        </td>
                        <td>
                            {{ $followUp->scheduled_date->format('d/m/Y') }}
                            <br>
                            <small class="text-muted">{{ $followUp->scheduled_date->format('h:i A') }}</small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $followUp->priority_color }}">
                                {{ ucfirst($followUp->priority) }}
                            </span>
                        </td>
                        <td>
                            @if($followUp->status === 'overdue')
                                <span class="badge bg-danger">Overdue</span>
                            @elseif($followUp->status === 'scheduled')
                                @if($followUp->scheduled_date->isToday())
                                    <span class="badge bg-warning">Today</span>
                                @else
                                    <span class="badge bg-info">Scheduled</span>
                                @endif
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($followUp->status) }}</span>
                            @endif
                        </td>
                        <td>{{ $followUp->assignedTo->name ?? '-' }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                @can('followups.view')
                                <a href="{{ route('followups.show', $followUp) }}" 
                                   class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                
                                @if($followUp->status !== 'completed')
                                    @can('followups.complete')
                                    <a href="{{ route('followups.complete', $followUp) }}" 
                                       class="btn btn-sm btn-outline-success" title="Complete">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('followups.edit')
                                    <button type="button" class="btn btn-sm btn-outline-warning reschedule-btn" 
                                            title="Reschedule" data-id="{{ $followUp->id }}"
                                            data-date="{{ $followUp->scheduled_date->format('Y-m-d\TH:i') }}">
                                        <i class="fas fa-clock"></i>
                                    </button>
                                    @endcan
                                    
                                    @can('followups.delete')
                                    <form action="{{ route('followups.destroy', $followUp) }}" 
                                          method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                <p>No follow-ups found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div>
                Showing {{ $followUps->firstItem() ?? 0 }} to {{ $followUps->lastItem() ?? 0 }} 
                of {{ $followUps->total() }} entries
            </div>
            
            {{ $followUps->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<!-- Reschedule Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rescheduleForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Reschedule Follow-up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="scheduled_date" class="form-label">New Date & Time</label>
                        <input type="datetime-local" class="form-control" name="scheduled_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason (Optional)</label>
                        <input type="text" class="form-control" name="reason" placeholder="Reason for rescheduling">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Reschedule</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    // Auto-submit filter form on change
    $('#filterForm select').on('change', function() {
        $('#filterForm').submit();
    });

    // Reschedule modal
    $('.reschedule-btn').on('click', function() {
        const followUpId = $(this).data('id');
        const currentDate = $(this).data('date');
        
        $('#rescheduleForm').attr('action', '/followups/' + followUpId + '/reschedule');
        $('#rescheduleForm input[name="scheduled_date"]').val(currentDate);
        $('#rescheduleModal').modal('show');
    });

    // Delete confirmation
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        
        if (confirm('Are you sure you want to delete this follow-up?')) {
            form.submit();
        }
    });
});
</script>
@endsection

