@extends('layouts.app')

@section('title', 'Follow-up Calendar')

@section('content')
<div class="page-header">
    <h1 class="page-title">Follow-up Calendar</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('followups.index') }}">Follow-ups</a></li>
            <li class="breadcrumb-item active">Calendar</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-3">
        <!-- Calendar Controls -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Calendar Options</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary" id="todayBtn">
                        <i class="fas fa-calendar-day me-2"></i> Today
                    </button>
                    @can('followups.create')
                    <a href="{{ route('followups.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i> New Follow-up
                    </a>
                    @endcan
                    <a href="{{ route('followups.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-2"></i> List View
                    </a>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Priority Legend</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="legend-color bg-secondary me-2"></div>
                    <span>Low Priority</span>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <div class="legend-color bg-primary me-2"></div>
                    <span>Medium Priority</span>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <div class="legend-color bg-warning me-2"></div>
                    <span>High Priority</span>
                </div>
                <div class="d-flex align-items-center">
                    <div class="legend-color bg-danger me-2"></div>
                    <span>Urgent Priority</span>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">This Month</h5>
            </div>
            <div class="card-body">
                <div id="monthStats">
                    <div class="text-center">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <!-- Calendar -->
        <div class="card">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

<!-- Event Detail Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Follow-up Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventDetails">
                <!-- Event details will be loaded here -->
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-primary" id="viewDetailsBtn">
                    <i class="fas fa-eye me-1"></i> View Full Details
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Create Modal -->
<div class="modal fade" id="quickCreateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="quickCreateForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Quick Add Follow-up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="quick_entity_type" class="form-label">Follow-up For</label>
                        <select class="form-select" id="quick_entity_type" name="entity_type" required>
                            <option value="">Select Type</option>
                            <option value="lead">Lead</option>
                            <option value="customer">Customer</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quick_entity_id" class="form-label">Select <span id="entityLabel">Entity</span></label>
                        <select class="form-select" id="quick_entity_id" name="entity_id" required>
                            <option value="">Select...</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quick_follow_up_type" class="form-label">Type</label>
                        <select class="form-select" id="quick_follow_up_type" name="follow_up_type" required>
                            <option value="phone_call">Phone Call</option>
                            <option value="email">Email</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="in_person_meeting">In-Person Meeting</option>
                            <option value="video_call">Video Call</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quick_priority" class="form-label">Priority</label>
                        <select class="form-select" id="quick_priority" name="priority" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quick_scheduled_date" class="form-label">Date & Time</label>
                        <input type="datetime-local" class="form-control" id="quick_scheduled_date" 
                               name="scheduled_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quick_subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="quick_subject" name="subject" 
                               required placeholder="Brief subject">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Follow-up
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
.fc-event {
    cursor: pointer;
    padding: 2px 5px;
    margin-bottom: 2px;
    border-radius: 3px;
}
.fc-event-title {
    font-weight: 600;
}
.fc-daygrid-event {
    white-space: normal;
}
.legend-color {
    width: 20px;
    height: 20px;
    border-radius: 3px;
}
.fc-day-today {
    background-color: #f8f9fa !important;
}
.fc-button-primary {
    background-color: #007bff;
    border-color: #007bff;
}
.fc-button-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}
.fc-button-primary:disabled {
    background-color: #007bff;
    border-color: #007bff;
}
.fc-button-primary:not(:disabled):active,
.fc-button-primary:not(:disabled).fc-button-active {
    background-color: #0056b3;
    border-color: #0056b3;
}
</style>
@endpush

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize calendar
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        events: @json($events),
        eventClick: function(info) {
            showEventDetails(info.event);
        },
        dateClick: function(info) {
            @can('followups.create')
            showQuickCreate(info.dateStr);
            @endcan
        },
        height: 'auto',
        eventDisplay: 'block',
        displayEventTime: false,
        eventMouseEnter: function(info) {
            // Show tooltip on hover
            $(info.el).tooltip({
                title: info.event.extendedProps.entity + ' - ' + info.event.extendedProps.priority + ' priority',
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
        }
    });
    
    calendar.render();
    
    // Today button
    $('#todayBtn').on('click', function() {
        calendar.today();
    });
    
    // Load month statistics
    loadMonthStats();
    
    // Event detail modal
    function showEventDetails(event) {
        const props = event.extendedProps;
        const typeIcon = getTypeIcon(props.type);
        const priorityBadge = getPriorityBadge(props.priority);
        
        let html = `
            <div class="mb-3">
                <h6><i class="${typeIcon} me-2"></i>${event.title}</h6>
                ${priorityBadge}
            </div>
            <table class="table table-sm">
                <tr>
                    <th width="35%">Date & Time:</th>
                    <td>${new Date(event.start).toLocaleString()}</td>
                </tr>
                <tr>
                    <th>${props.entity_type}:</th>
                    <td>${props.entity}</td>
                </tr>
                <tr>
                    <th>Type:</th>
                    <td>${formatType(props.type)}</td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td><span class="badge bg-${props.status === 'overdue' ? 'danger' : 'info'}">${props.status}</span></td>
                </tr>
                ${props.assigned_to ? `
                <tr>
                    <th>Assigned To:</th>
                    <td>${props.assigned_to}</td>
                </tr>` : ''}
            </table>
        `;
        
        $('#eventDetails').html(html);
        $('#viewDetailsBtn').attr('href', event.url);
        $('#eventModal').modal('show');
    }
    
    // Quick create modal
    function showQuickCreate(dateStr) {
        const date = new Date(dateStr);
        date.setHours(10, 0, 0, 0);
        
        // Format for datetime-local input
        const formattedDate = date.toISOString().slice(0, 16);
        $('#quick_scheduled_date').val(formattedDate);
        
        $('#quickCreateModal').modal('show');
    }
    
    // Entity type change
    $('#quick_entity_type').on('change', function() {
        const type = $(this).val();
        const $entitySelect = $('#quick_entity_id');
        const $entityLabel = $('#entityLabel');
        
        $entitySelect.empty().append('<option value="">Loading...</option>');
        $entityLabel.text(type === 'lead' ? 'Lead' : 'Customer');
        
        if (type) {
            $.get('/api/' + type + 's', function(data) {
                $entitySelect.empty().append('<option value="">Select ' + type + '</option>');
                
                data.forEach(function(item) {
                    const text = type === 'lead' 
                        ? item.lead_no + ' - ' + (item.company_name || item.contact_person)
                        : item.customer_code + ' - ' + item.company_name;
                    
                    $entitySelect.append(new Option(text, item.id));
                });
            });
        }
    });
    
    // Quick create form submission
    $('#quickCreateForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serializeArray();
        const data = {};
        formData.forEach(item => data[item.name] = item.value);
        
        $.ajax({
            url: '/api/followups/quick-create',
            method: 'POST',
            data: data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Add event to calendar
                    calendar.addEvent({
                        id: response.follow_up.id,
                        title: response.follow_up.subject,
                        start: response.follow_up.scheduled_date,
                        color: getPriorityColor(response.follow_up.priority),
                        url: '/followups/' + response.follow_up.id,
                        extendedProps: {
                            type: response.follow_up.follow_up_type,
                            entity: response.follow_up.entity_name,
                            entity_type: response.follow_up.entity_type,
                            status: response.follow_up.status,
                            priority: response.follow_up.priority
                        }
                    });
                    
                    $('#quickCreateModal').modal('hide');
                    $('#quickCreateForm')[0].reset();
                    
                    // Show success message
                    toastr.success(response.message);
                    
                    // Reload stats
                    loadMonthStats();
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                let errorMsg = 'Please check the form and try again.';
                
                if (errors) {
                    errorMsg = Object.values(errors).flat().join('<br>');
                }
                
                toastr.error(errorMsg);
            }
        });
    });
    
    // Load month statistics
    function loadMonthStats() {
        $.get('/api/followups/stats', function(data) {
            let html = `
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Active:</span>
                    <strong>${data.this_month}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Completed:</span>
                    <strong class="text-success">${data.completed_this_month}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Overdue:</span>
                    <strong class="text-danger">${data.overdue}</strong>
                </div>
                <hr>
                <h6 class="mb-2">By Type:</h6>
            `;
            
            if (data.by_type) {
                Object.entries(data.by_type).forEach(([type, count]) => {
                    html += `
                        <div class="d-flex justify-content-between mb-1">
                            <small>${formatType(type)}:</small>
                            <small>${count}</small>
                        </div>
                    `;
                });
            }
            
            $('#monthStats').html(html);
        });
    }
    
    // Helper functions
    function getTypeIcon(type) {
        const icons = {
            'phone_call': 'fas fa-phone',
            'email': 'fas fa-envelope',
            'whatsapp': 'fab fa-whatsapp',
            'in_person_meeting': 'fas fa-handshake',
            'video_call': 'fas fa-video',
            'other': 'fas fa-comment-dots'
        };
        return icons[type] || 'fas fa-calendar';
    }
    
    function getPriorityColor(priority) {
        const colors = {
            'low': '#6c757d',
            'medium': '#007bff',
            'high': '#ffc107',
            'urgent': '#dc3545'
        };
        return colors[priority] || '#6c757d';
    }
    
    function getPriorityBadge(priority) {
        const colors = {
            'low': 'secondary',
            'medium': 'primary',
            'high': 'warning',
            'urgent': 'danger'
        };
        return `<span class="badge bg-${colors[priority] || 'secondary'}">${priority.toUpperCase()}</span>`;
    }
    
    function formatType(type) {
        return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
});
</script>
@endpush