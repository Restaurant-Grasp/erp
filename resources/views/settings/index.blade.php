@extends('layouts.app')
@section('title', 'System Settings')
@section('content')

@push('styles')
<style>
    .settings-tab-content {
        min-height: 400px;
    }

    .setting-item {
        border-bottom: 1px solid #e9ecef;
        padding: 15px 0;
    }

    .setting-item:last-child {
        border-bottom: none;
    }

    .setting-label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 5px;
    }

    .setting-description {
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 10px;
    }

    .setting-type-badge {
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 3px;
    }

    .nav-tabs .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        color: #6c757d;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }

    .nav-tabs .nav-link.active {
        color: var(--primary-green);
        border-bottom-color: var(--primary-green);
        background-color: transparent;
    }

    .settings-form-section {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .file-upload-section .current-file {
        padding: 10px;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 5px;
    }

    .file-upload-section .img-thumbnail {
        border: 2px solid #dee2e6;
    }

    .company-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .company-logos {
        display: flex;
        gap: 20px;
        align-items: start;
        flex-wrap: wrap;
    }

    .logo-section {
        flex: 1;
        min-width: 250px;
    }

    .timezone-dropdown,
    .currency-dropdown {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }

    .timezone-info,
    .currency-info {
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
    }

    .country-info-display {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .country-flag {
        font-size: 2rem;
        margin-right: 10px;
    }

    .auto-detect-badge {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        padding: 4px 8px;
        border-radius: 15px;
        font-size: 11px;
        margin-left: 10px;
    }

    .country-change-notification {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 15px;
        display: none;
    }

    .currency-timezone-group {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        position: relative;
    }

    .currency-timezone-group::before {
        content: '🌍';
        position: absolute;
        top: -10px;
        left: 20px;
        background: white;
        padding: 0 10px;
        font-size: 20px;
    }

    .setting-input-with-feedback {
        position: relative;
    }

    .setting-feedback {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
    }
</style>
@endpush

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">System Settings</h5>
        @php
        $role = auth()->user()->getRoleNames()->first();
        $permissions = getCurrentRolePermissions($role);
        @endphp
        @if ($permissions->contains('name', 'settings.create'))
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSettingModal">
            <i class="fas fa-plus me-2"></i>Add Setting
        </button>
        @endif
    </div>

    <div class="card-body">
        @if($categories && count($categories) > 0)

        <!-- Country Change Notification -->
        <div id="countryChangeNotification" class="country-change-notification">
            <i class="fas fa-info-circle me-2"></i>
            <span id="countryChangeText"></span>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
            @foreach($categories as $index => $category)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                    id="{{ $category }}-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#{{ $category }}-content"
                    type="button"
                    role="tab"
                    aria-controls="{{ $category }}-content"
                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                    <i class="fas fa-{{ getCategoryIcon($category) }} me-2"></i>
                    {{ ucfirst($category) }}
                    <span class="badge bg-secondary ms-2">{{ count($settingsByCategory[$category]) }}</span>
                </button>
            </li>
            @endforeach
        </ul>

        <!-- Settings Form -->
        <form method="POST" action="{{ route('settings.update') }}" class="mt-4" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Tab Content -->
            <div class="tab-content settings-tab-content" id="settingsTabContent">
                @foreach($categories as $index => $category)
                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                    id="{{ $category }}-content"
                    role="tabpanel"
                    aria-labelledby="{{ $category }}-tab">

                    @if($category === 'general')
                    {{-- Special layout for general settings with currency/timezone/country group --}}
                    <div class="settings-form-section">
                        <h6 class="text-muted mb-4">
                            <i class="fas fa-globe me-2"></i>
                            Regional Settings
                        </h6>

                        {{-- Currency, Timezone, and Country Group --}}
                        <div class="currency-timezone-group">
                            <h6 class="fw-bold text-dark mb-3">Location & Currency Configuration</h6>
                            <div class="row">
                                @foreach($settingsByCategory[$category]->whereIn('setting_key', ['currency', 'time_zone', 'country']) as $setting)
                                <div class="col-md-4 mb-3">
                                    <div class="setting-label">
                                        {{ $setting->display_key }}
                                        <span class="setting-type-badge bg-light text-muted ms-2">
                                            {{ strtoupper($setting->setting_type) }}
                                        </span>
                                    </div>
                                    @if($setting->description)
                                    <div class="setting-description">
                                        {{ $setting->description }}
                                    </div>
                                    @endif

                                    <div class="setting-input-with-feedback">
                                        @if($setting->setting_key === 'country')
                                        <select class="form-control currency-dropdown"
                                            name="settings[{{ $setting->id }}]"
                                            id="currency-select">
                                            <option value="">-- Select Country --</option>
                                            @foreach(getSupportedCurrencies() as $code => $details)
                                            <option value="{{ $code }}"
                                                {{ $setting->setting_value == $code ? 'selected' : '' }}>
                                                {{ $details }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <div class="currency-info">
                                            <i class="fas fa-coins me-1"></i>
                                            <span id="currency-info-text">{{ $setting->setting_value ?: 'No currency selected' }}</span>
                                        </div>

                                        @elseif($setting->setting_key === 'time_zone')
                                        <select class="form-control timezone-dropdown"
                                            name="settings[{{ $setting->id }}]"
                                            id="timezone-select">
                                            <option value="">-- Select Timezone --</option>
                                            @foreach(getTimezoneList() as $timezone => $label)
                                            <option value="{{ $timezone }}"
                                                {{ $setting->setting_value == $timezone ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <div class="timezone-info">
                                            <i class="fas fa-clock me-1"></i>
                                            Current: <span id="current-time-{{ $setting->id }}">Loading...</span>
                                        </div>
                                        @elseif($setting->setting_key === 'currency')

                                        <input type="text"
                                            class="form-control"
                                            name="settings[{{ $setting->id }}]"
                                            id="country-input"
                                            value="{{ $setting->setting_value }}"
                                            readonly
                                            placeholder="Auto-detected">
                                        <div class="setting-feedback">
                                            <i class="fas fa-robot text-muted" title="Auto-detected"></i>
                                        </div>
                                        <div class="currency-info">
                                            <i class="fas fa-flag me-1"></i>
                                            <span id="country-display">{{ isset($currentCountryInfo) ? $currentCountryInfo['flag'] . ' ' . $currentCountryInfo['country_name'] : 'Auto-detected' }}</span>
                                        </div>

                                        @else
                                        <input type="text"
                                            class="form-control"
                                            name="settings[{{ $setting->id }}]"
                                            value="{{ $setting->setting_value }}"
                                            placeholder="Enter {{ strtolower($setting->display_key) }}">
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Other General Settings --}}
                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-bold text-dark mb-3">Other General Settings</h6>
                                <div class="company-info-grid">
                                    @foreach($settingsByCategory[$category]->whereNotIn('setting_key', ['currency', 'time_zone', 'country']) as $setting)
                                    <div class="setting-item border rounded p-3 bg-white">
                                        <!-- Standard setting display code here -->
                                        <div class="setting-label">
                                            {{ $setting->display_key }}
                                            <span class="setting-type-badge bg-light text-muted ms-2">
                                                {{ strtoupper($setting->setting_type) }}
                                            </span>
                                        </div>
                                        @if($setting->description)
                                        <div class="setting-description">
                                            {{ $setting->description }}
                                        </div>
                                        @endif

                                        <div class="mt-2">
                                            @if($setting->setting_type === 'file')
                                            <!-- File upload code -->
                                            @elseif($setting->setting_key === 'date_format')
                                            <select class="form-control" name="settings[{{ $setting->id }}]">
                                                <option value="d-m-Y" {{ $setting->setting_value == 'd-m-Y' ? 'selected' : '' }}>DD-MM-YYYY</option>
                                                <option value="m-d-Y" {{ $setting->setting_value == 'm-d-Y' ? 'selected' : '' }}>MM-DD-YYYY</option>
                                                <option value="Y-m-d" {{ $setting->setting_value == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                                                <option value="d/m/Y" {{ $setting->setting_value == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                                                <option value="m/d/Y" {{ $setting->setting_value == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                                            </select>
                                            @else
                                            <input type="text"
                                                class="form-control"
                                                name="settings[{{ $setting->id }}]"
                                                value="{{ $setting->setting_value }}"
                                                placeholder="Enter {{ strtolower($setting->display_key) }}">
                                            @endif
                                        </div>

                                        @if ($permissions->contains('name', 'settings.delete'))
                                        <div class="mt-2 text-end">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="deleteSetting({{ $setting->id }})"
                                                title="Delete Setting">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    {{-- Original layout for other categories --}}
                    <div class="settings-form-section">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-{{ getCategoryIcon($category) }} me-2"></i>
                            {{ ucfirst($category) }} Settings
                        </h6>

                        @foreach($settingsByCategory[$category] as $setting)
                        <div class="setting-item">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <div class="setting-label">
                                        {{ $setting->display_key }}
                                        <span class="setting-type-badge bg-light text-muted ms-2">
                                            {{ strtoupper($setting->setting_type) }}
                                        </span>
                                    </div>
                                    @if($setting->description)
                                    <div class="setting-description">
                                        {{ $setting->description }}
                                    </div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    @if($setting->setting_type === 'file')
                                    <div class="file-upload-section">
                                        @if($setting->setting_value)
                                        <div class="current-file mb-2">
                                            @if(in_array(pathinfo($setting->setting_value, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'svg']))
                                            <img src="{{ asset('assets/' . $setting->setting_value) }}"
                                                alt="Current {{ $setting->display_key }}"
                                                class="img-thumbnail"
                                                style="max-height: 100px;">
                                            @else
                                            <a href="{{ asset('assets/' . $setting->setting_value) }}"
                                                target="_blank"
                                                class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-download me-1"></i>View File
                                            </a>
                                            @endif
                                            <div class="small text-muted mt-1">
                                                Current: {{ basename($setting->setting_value) }}
                                            </div>
                                        </div>
                                        @endif
                                        <input type="file"
                                            class="form-control"
                                            name="files[{{ $setting->id }}]"
                                            accept="image/*"
                                            onchange="previewFile(this, {{ $setting->id }})">
                                        <div class="small text-muted mt-1">
                                            Accepted: JPG, JPEG, PNG, SVG, GIF (Max: 2MB)
                                        </div>
                                        <div id="preview_{{ $setting->id }}" class="mt-2"></div>
                                    </div>
                                    @elseif($setting->setting_type === 'boolean')
                                    <div class="form-check form-switch">
                                        <input class="form-check-input"
                                            type="checkbox"
                                            name="settings[{{ $setting->id }}]"
                                            id="setting_{{ $setting->id }}"
                                            value="1"
                                            {{ $setting->setting_value == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="setting_{{ $setting->id }}">
                                            {{ $setting->setting_value == '1' ? 'Enabled' : 'Disabled' }}
                                        </label>
                                    </div>
                                    @elseif($setting->setting_type === 'number')
                                    <input type="number"
                                        class="form-control"
                                        name="settings[{{ $setting->id }}]"
                                        value="{{ $setting->setting_value }}"
                                        step="any">
                                    @elseif($setting->setting_type === 'json')
                                    <textarea class="form-control"
                                        name="settings[{{ $setting->id }}]"
                                        rows="3"
                                        placeholder="Valid JSON format">{{ $setting->setting_value }}</textarea>
                                    @elseif($setting->setting_type === 'longtext')
                                    {{-- Special handling for terms and conditions - large textarea --}}
                                    <div class="terms-conditions-section">
                                        <textarea class="form-control"
                                            name="settings[{{ $setting->id }}]"
                                            rows="12"
                                            placeholder="Enter terms and conditions..."
                                            style="font-family: 'Courier New', monospace; font-size: 12px;">{{ $setting->setting_value }}</textarea>
                                        <div class="small text-muted mt-1">
                                            <i class="fas fa-info-circle me-1"></i>
                                            This will be used as default terms and conditions for sales documents (quotations, invoices, etc.)
                                        </div>
                                        <div class="small text-muted mt-1">
                                            Character count: <span id="char-count-{{ $setting->id }}">{{ strlen($setting->setting_value) }}</span>
                                        </div>
                                    </div>

                                    @else
                                    <input type="text"
                                        class="form-control"
                                        name="settings[{{ $setting->id }}]"
                                        value="{{ $setting->setting_value }}"
                                        placeholder="Enter {{ strtolower($setting->display_key) }}">
                                    @endif
                                </div>

                                <div class="col-md-2 text-end">
                                    @if ($permissions->contains('name', 'settings.delete'))
                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="deleteSetting({{ $setting->id }})"
                                        title="Delete Setting">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- Save Button -->
            @if ($permissions->contains('name', 'settings.edit'))
            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Settings
                </button>
            </div>
            @endif
        </form>
        @else
        <div class="text-center py-5">
            <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
            <h6 class="text-muted">No settings found</h6>
            <p class="text-muted">Start by adding your first system setting.</p>
        </div>
        @endif
    </div>
</div>

<!-- Add Setting Modal -->
<div class="modal fade" id="addSettingModal" tabindex="-1" aria-labelledby="addSettingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('settings.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addSettingModalLabel">Add New Setting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-control" required>
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                            <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                            @endforeach
                            <option value="custom">Custom Category</option>
                        </select>
                    </div>

                    <div class="mb-3" id="customCategoryField" style="display: none;">
                        <label class="form-label">Custom Category Name</label>
                        <input type="text" name="custom_category" class="form-control" placeholder="Enter category name">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Setting Key <span class="text-danger">*</span></label>
                        <input type="text" name="setting_key" class="form-control" required placeholder="e.g., max_upload_size">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Setting Type <span class="text-danger">*</span></label>
                        <select name="setting_type" class="form-control" required>
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="boolean">Boolean</option>
                            <option value="json">JSON</option>
                            <option value="file">File</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Setting Value</label>
                        <input type="text" name="setting_value" class="form-control" placeholder="Enter default value">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief description of this setting"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Setting</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Handle custom category field
    $('select[name="category"]').change(function() {
        if ($(this).val() === 'custom') {
            $('#customCategoryField').show();
            $('input[name="custom_category"]').attr('required', true);
        } else {
            $('#customCategoryField').hide();
            $('input[name="custom_category"]').attr('required', false);
        }
    });

    // Handle switch labels
    $('.form-check-input[type="checkbox"]').change(function() {
        const label = $(this).siblings('.form-check-label');
        if ($(this).is(':checked')) {
            label.text('Enabled');
        } else {
            label.text('Disabled');
        }
    });

    // Preview file upload
    function previewFile(input, settingId) {
        const preview = document.getElementById('preview_' + settingId);
        const file = input.files[0];

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `
                    <div class="mt-2">
                        <strong class="small text-success">New file selected:</strong>
                        <div class="mt-1">
                            <img src="${e.target.result}" alt="Preview" class="img-thumbnail" style="max-height: 80px;">
                        </div>
                        <div class="small text-muted">${file.name} (${(file.size / 1024).toFixed(1)} KB)</div>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '';
        }
    }

    // Update timezone current time
    function updateTimezoneTime() {
        $('.timezone-dropdown').each(function() {
            const settingId = $(this).attr('name').match(/\d+/)[0];
            const timezone = $(this).val();
            const timeElement = $('#current-time-' + settingId);

            if (timezone) {
                try {
                    const now = new Date();
                    const timeString = now.toLocaleString('en-US', {
                        timeZone: timezone,
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false
                    });
                    timeElement.text(timeString);
                } catch (e) {
                    timeElement.text('Invalid timezone');
                }
            } else {
                timeElement.text('No timezone selected');
            }
        });
    }

    // Handle currency and timezone changes for country auto-detection
    function updateCountryBasedOnCurrencyTimezone() {
        const currency = $('#currency-select').val();
        const timezone = $('#timezone-select').val();

        if (currency || timezone) {
            // Make AJAX call to get country information
            $.ajax({
                url: '/settings/get-country-info',
                method: 'GET',
                data: {
                    currency: currency,
                    timezone: timezone,
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    // Update country input
                    $('#country-input').val(data.symbol);

                    // Update country display
                    $('#country-display').text(data.flag + ' ' + data.country_name);

                    // Show notification
                    $('#countryChangeText').text(`Country will be updated to ${data.flag} ${data.country_name} based on your currency and timezone selection.`);
                    $('#countryChangeNotification').slideDown();

                    // Update currency info
                    $('#currency-info-text').text(currency || 'No currency selected');
                },
                error: function() {
                    console.log('Could not detect country');
                }
            });
        }
    }

    // Handle currency change
    $('#currency-select').change(function() {
        updateCountryBasedOnCurrencyTimezone();
    });

    // Handle timezone change
    $('#timezone-select').change(function() {
        updateTimezoneTime();
        updateCountryBasedOnCurrencyTimezone();
    });

    // Update time every second
    setInterval(updateTimezoneTime, 1000);

    // Initial load
    $(document).ready(function() {
        updateTimezoneTime();
    });

    // Delete setting function
    function deleteSetting(settingId) {
        if (confirm('Are you sure you want to delete this setting? This action cannot be undone.')) {
            // Create a form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/settings/' + settingId;

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';

            form.appendChild(csrfInput);
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endpush

@php
function getCategoryIcon($category) {
$icons = [
'company' => 'building',
'general' => 'globe',
'sales' => 'chart-line',
'purchase' => 'shopping-cart',
'service' => 'tools',
'subscription' => 'sync',
'hr' => 'users',
'email' => 'envelope',
'system' => 'server',
'security' => 'shield-alt',
'api' => 'plug',
];

return $icons[$category] ?? 'cog';
}

function getTimezoneList() {
return [
// Asia Pacific (Most Common)
'Asia/Kuala_Lumpur' => '(GMT+08:00) Kuala Lumpur, Singapore',
'Asia/Jakarta' => '(GMT+07:00) Jakarta',
'Asia/Bangkok' => '(GMT+07:00) Bangkok, Hanoi',
'Asia/Manila' => '(GMT+08:00) Manila',
'Asia/Hong_Kong' => '(GMT+08:00) Hong Kong',
'Asia/Shanghai' => '(GMT+08:00) Beijing, Shanghai',
'Asia/Tokyo' => '(GMT+09:00) Tokyo, Osaka',
'Asia/Seoul' => '(GMT+09:00) Seoul',
'Asia/Kolkata' => '(GMT+05:30) Mumbai, Delhi, Kolkata',
'Asia/Karachi' => '(GMT+05:00) Karachi, Islamabad',
'Asia/Dubai' => '(GMT+04:00) Dubai, Abu Dhabi',
'Asia/Riyadh' => '(GMT+03:00) Riyadh, Kuwait',

// Europe
'Europe/London' => '(GMT+00:00) London, Dublin, Edinburgh',
'Europe/Paris' => '(GMT+01:00) Paris, Berlin, Madrid',
'Europe/Amsterdam' => '(GMT+01:00) Amsterdam, Brussels',
'Europe/Zurich' => '(GMT+01:00) Zurich, Vienna',
'Europe/Moscow' => '(GMT+03:00) Moscow, St. Petersburg',
'Europe/Istanbul' => '(GMT+03:00) Istanbul',
'Europe/Athens' => '(GMT+02:00) Athens, Helsinki',

// Americas
'America/New_York' => '(GMT-05:00) New York, Toronto',
'America/Chicago' => '(GMT-06:00) Chicago, Dallas',
'America/Denver' => '(GMT-07:00) Denver, Salt Lake City',
'America/Phoenix' => '(GMT-07:00) Phoenix (No DST)',
'America/Los_Angeles' => '(GMT-08:00) Los Angeles, San Francisco',
'America/Sao_Paulo' => '(GMT-03:00) São Paulo, Rio de Janeiro',
'America/Buenos_Aires' => '(GMT-03:00) Buenos Aires',
'America/Lima' => '(GMT-05:00) Lima, Bogota',

// Australia & Pacific
'Australia/Sydney' => '(GMT+10:00) Sydney, Melbourne',
'Australia/Brisbane' => '(GMT+10:00) Brisbane',
'Australia/Perth' => '(GMT+08:00) Perth',
'Australia/Adelaide' => '(GMT+09:30) Adelaide',
'Pacific/Auckland' => '(GMT+12:00) Auckland, Wellington',
'Pacific/Honolulu' => '(GMT-10:00) Honolulu',

// Africa
'Africa/Cairo' => '(GMT+02:00) Cairo',
'Africa/Johannesburg' => '(GMT+02:00) Johannesburg, Cape Town',
'Africa/Lagos' => '(GMT+01:00) Lagos, Kinshasa',
'Africa/Nairobi' => '(GMT+03:00) Nairobi, Addis Ababa',

// UTC & Other
'UTC' => '(GMT+00:00) UTC - Universal Coordinated Time',
'GMT' => '(GMT+00:00) Greenwich Mean Time',
];
}
function getSupportedCurrencies() {
return [
'MYR' => '🇲🇾 MYR (RM) - Malaysia',
'SGD' => '🇸🇬 SGD (S$) - Singapore',
'IDR' => '🇮🇩 IDR (Rp) - Indonesia',
'THB' => '🇹🇭 THB (฿) - Thailand',
'PHP' => '🇵🇭 PHP (₱) - Philippines',
'INR' => '🇮🇳 INR (₹) - India',
'AED' => '🇦🇪 AED (د.إ) - United Arab Emirates',
'USD' => '🇺🇸 USD ($) - United States',
'GBP' => '🇬🇧 GBP (£) - United Kingdom',
'EUR' => '🇪🇺 EUR (€) - European Union',
'JPY' => '🇯🇵 JPY (¥) - Japan',
'AUD' => '🇦🇺 AUD (A$) - Australia',
'CAD' => '🇨🇦 CAD (C$) - Canada',
'CNY' => '🇨🇳 CNY (¥) - China',
];
}
@endphp
@endsection