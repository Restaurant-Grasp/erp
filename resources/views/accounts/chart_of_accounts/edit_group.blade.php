@extends('layouts.app')
@section('title', 'Edit Account Group')

@section('content')
<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb pl-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('chart_of_accounts.index') }}">Chart of Accounts</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Group</li>
            </ol>
        </nav>
        <br>
        <div class="card">
            <div class="card-header">
               <h5>  Edit Account Group: {{ $group->name }}</h5>
            </div>
            
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
        
                
                <form method="POST" action="{{ route('chart_of_accounts.group.update', $group->id) }}" id="groupForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Group Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $group->name) }}" required 
                                       placeholder="Enter group name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Enter a descriptive name for the account group</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="code" class="form-label">Group Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" value="{{ old('code', $group->code) }}" required 
                                       maxlength="4" pattern="[0-9]{4}" placeholder="e.g., 1110">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    4-digit numeric code. <span id="code-range-hint" class="text-info"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group mb-3">
                                <label for="parent_id" class="form-label">Parent Group <span class="text-danger">*</span></label>
                                <select class="form-select @error('parent_id') is-invalid @enderror" 
                                        id="parent_id" name="parent_id" required>
                                    <option value="">-- Select Parent Group --</option>
                                    @foreach($parentGroups as $parentGroup)
                                        <option value="{{ $parentGroup->id }}" 
                                                data-code="{{ $parentGroup->code }}"
                                                {{ old('parent_id', $group->parent_id) == $parentGroup->id ? 'selected' : '' }}>
                                            {!! $parentGroup->display_name !!}
                                        </option>
                                    @endforeach
                                </select>
                                @error('parent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Groups must be under existing parent groups. Cannot select itself or descendants.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Code Range Information Card -->
                    <div class="row" id="code-info-card" style="display: none;">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Code Range Guidelines</h6>
                                <div id="code-guidelines"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Warning about dependencies -->
                    @if($group->ledgers->count() > 0 || $group->children->count() > 0)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle"></i> Important Notice</h6>
                                    <p class="mb-2">This group has:</p>
                                    <ul class="mb-2">
                                        @if($group->ledgers->count() > 0)
                                            <li><strong>{{ $group->ledgers->count() }} ledger(s)</strong> - Changes may affect existing transactions</li>
                                        @endif
                                        @if($group->children->count() > 0)
                                            <li><strong>{{ $group->children->count() }} sub-group(s)</strong> - Code changes may affect hierarchy</li>
                                        @endif
                                    </ul>
                                    <p class="mb-0 small">Please ensure changes are necessary and won't disrupt existing data.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Update Group
                                </button>
                              <a href="{{ route('chart_of_accounts.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            
                         
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<style>
.form-label {
    font-weight: 600;
    color: #495057;
}



.alert-info {
    background-color: #e8f4fd;
    border-color: #bee5eb;
    color: #0c5460;
}

.alert-light {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

#code-range-hint {
    font-weight: 600;
}

.badge {
    font-size: 0.875em;
}
</style>

<script>
$(document).ready(function() {
    // Store original values
    window.originalValues = {
        name: $('#name').val(),
        code: $('#code').val(),
        parent_id: $('#parent_id').val()
    };
    
    // Update code range hint when parent is selected
    $('#parent_id').change(function() {
        updateCodeRangeHint();
    });
    
    // Initialize on page load
    updateCodeRangeHint();
    
    // Form validation
    $('#groupForm').on('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
    });
    
    // Real-time code validation
    $('#code').on('input', function() {
        validateCode();
    });
    
    // Check for changes before leaving
    window.addEventListener('beforeunload', function(e) {
        if (hasUnsavedChanges()) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});

function updateCodeRangeHint() {
    var selectedOption = $('#parent_id option:selected');
    var parentCode = selectedOption.data('code');
    
    if (parentCode) {
        // Find the base code (top-level parent code)
        var baseCode = Math.floor(parentCode / 1000) * 1000;
        var rangeStart = baseCode + 1;
        var rangeEnd = baseCode + 999;
        
        $('#code-range-hint').text('Valid range: ' + rangeStart + '-' + rangeEnd);
        
        // Show guidelines
        showCodeGuidelines(baseCode, rangeStart, rangeEnd, selectedOption.text());
    } else {
        $('#code-range-hint').text('');
        $('#code-info-card').hide();
    }
}

function showCodeGuidelines(baseCode, rangeStart, rangeEnd, parentName) {
    var groupType = getGroupType(baseCode);
    var currentCode = {{ $group->code }};
    var guidelines = `
        <p><strong>Parent Group:</strong> ${parentName}</p>
        <p><strong>Group Type:</strong> ${groupType}</p>
        <p><strong>Valid Code Range:</strong> ${rangeStart} - ${rangeEnd}</p>
        <p><strong>Current Code:</strong> ${currentCode}</p>
        <p><small><strong>Note:</strong> Changing the code may affect reporting and hierarchy. Ensure the new code maintains logical grouping.</small></p>
    `;
    
    $('#code-guidelines').html(guidelines);
    $('#code-info-card').show();
}

function getGroupType(baseCode) {
    if (baseCode >= 1000 && baseCode <= 1999) return 'Assets';
    if (baseCode >= 2000 && baseCode <= 2999) return 'Liabilities';
    if (baseCode >= 3000 && baseCode <= 3999) return 'Equity';
    if (baseCode >= 4000 && baseCode <= 4999) return 'Revenue';
    if (baseCode >= 5000 && baseCode <= 5999) return 'Direct Cost';
    if (baseCode >= 6000 && baseCode <= 6999) return 'Expenses';
    if (baseCode >= 8000 && baseCode <= 8999) return 'Other Income';
    if (baseCode >= 9000 && baseCode <= 9999) return 'Taxation';
    return 'Unknown';
}

function validateCode() {
    var code = $('#code').val();
    var parentCode = $('#parent_id option:selected').data('code');
    
    if (code && parentCode) {
        var codeInt = parseInt(code);
        var baseCode = Math.floor(parentCode / 1000) * 1000;
        var rangeStart = baseCode + 1;
        var rangeEnd = baseCode + 999;
        
        var codeInput = $('#code');
        
        if (codeInt < rangeStart || codeInt > rangeEnd || codeInt == baseCode) {
            codeInput.removeClass('is-valid').addClass('is-invalid');
            showCodeError(`Code must be between ${rangeStart} and ${rangeEnd}`);
        } else {
            codeInput.removeClass('is-invalid').addClass('is-valid');
            hideCodeError();
        }
    }
}

function showCodeError(message) {
    $('#code').siblings('.invalid-feedback').remove();
    $('#code').after(`<div class="invalid-feedback">${message}</div>`);
}

function hideCodeError() {
    $('#code').siblings('.invalid-feedback').remove();
}

function validateForm() {
    var isValid = true;
    
    // Validate name
    if (!$('#name').val().trim()) {
        showFieldError('#name', 'Group name is required');
        isValid = false;
    }
    
    // Validate code
    if (!$('#code').val().trim()) {
        showFieldError('#code', 'Group code is required');
        isValid = false;
    } else if (!/^[0-9]{4}$/.test($('#code').val())) {
        showFieldError('#code', 'Group code must be exactly 4 digits');
        isValid = false;
    }
    
    // Validate parent
    if (!$('#parent_id').val()) {
        showFieldError('#parent_id', 'Parent group is required');
        isValid = false;
    }
    
    // Check for circular reference (parent cannot be self)
    if ($('#parent_id').val() == {{ $group->id }}) {
        showFieldError('#parent_id', 'A group cannot be its own parent');
        isValid = false;
    }
    
    return isValid;
}

function showFieldError(field, message) {
    $(field).addClass('is-invalid');
    $(field).siblings('.invalid-feedback').remove();
    $(field).after(`<div class="invalid-feedback">${message}</div>`);
}

function resetToOriginal() {
    if (confirm('Are you sure you want to reset all changes?')) {
        $('#name').val(window.originalValues.name);
        $('#code').val(window.originalValues.code);
        $('#parent_id').val(window.originalValues.parent_id);
        
        // Clear validation states
        $('.is-invalid').removeClass('is-invalid');
        $('.is-valid').removeClass('is-valid');
        $('.invalid-feedback').remove();
        
        // Update hints
        updateCodeRangeHint();
    }
}

function hasUnsavedChanges() {
    return $('#name').val() !== window.originalValues.name ||
           $('#code').val() !== window.originalValues.code ||
           $('#parent_id').val() !== window.originalValues.parent_id;
}
</script>

@endsection