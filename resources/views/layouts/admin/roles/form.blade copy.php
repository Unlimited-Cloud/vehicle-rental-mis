@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="m-0">
                <i class="fas {{ isset($role) ? 'fa-edit' : 'fa-plus-circle' }}"></i>
                {{ isset($role) ? 'Edit Role' : 'Add New Role' }}
            </h1>
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.user_roles.index') }}">Roles</a></li>
                <li class="breadcrumb-item active">{{ isset($role) ? 'Edit' : 'Add' }}</li>
            </ol>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @include('layouts.admin_theme.alert')

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-tag"></i>
                    {{ isset($role) ? 'Edit Role Information' : 'Role Information' }}
                </h3>
                <div class="card-tools">
                    <a href="{{ route('admin.user_roles.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            <div class="card-body">
                <form action="{{ isset($role) ? route('admin.user_roles.update', $role->id) : route('admin.user_roles.store') }}" 
                      method="POST" 
                      class="ajaxsubmit"
                      enctype="multipart/form-data">
                    @csrf
                    @if(isset($role))
                        @method('PUT')
                    @endif

                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">
                                    <i class="fas fa-tag text-primary"></i> Role Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="name" 
                                       id="name"
                                       class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $role->name ?? '') }}"
                                       placeholder="Enter role name"
                                       {{ isset($role) && $role->name == 'Admin' ? 'readonly' : '' }}>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Role name should be unique and descriptive (e.g., Admin, Driver, Helper,Finance)
                                </small>
                            </div>
                        </div>
                    </div>

                    @php
                        // Group permissions by module_name
                        $groupedPermissions = $permissions->groupBy('module_name');
                    @endphp

                    <!-- Permissions Section -->
                    <div class="mt-4">
                        <div class="card card-info card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-shield-alt"></i>
                                    Role Permissions
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <!-- Permission Selection Controls -->
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="btn-group" role="group">
                                           <button type="button" class="btn btn-outline-primary btn-sm" id="select-all-modules">
                                                <i class="fas fa-check-double"></i> Select All Modules
                                            </button>
                                           
                                        </div>
                                        <small class="text-muted ml-3">
                                            <i class="fas fa-info-circle"></i>
                                            Select modules to grant all permissions or select individually
                                        </small>
                                    </div>
                                </div>

                                <!-- Permissions Grid -->
                                <div class="row">
                                    @foreach ($groupedPermissions as $module_name => $permissionsGroup)
                                        <div class="col-md-4 col-sm-6 mb-4">
                                            <div class="card card-outline card-primary h-100">
                                                <div class="card-header py-2">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" 
                                                               class="custom-control-input module-checkbox" 
                                                               id="module-{{ Str::slug($module_name) }}">
                                                        <label class="custom-control-label font-weight-bold" 
                                                               for="module-{{ Str::slug($module_name) }}">
                                                            <i class="fas fa-cube"></i> {{ $module_name }}
                                                        </label>
                                                    </div>
                                                </div>
                                                
                                                <div class="card-body py-2">
                                                    @foreach ($permissionsGroup as $permission)
                                                        <div class="custom-control custom-checkbox mb-2">
                                                            <input type="checkbox" 
                                                                   class="custom-control-input permission-checkbox"
                                                                   name="permissions[]" 
                                                                   value="{{ $permission->id }}"
                                                                   id="permission-{{ $permission->id }}"
                                                                   data-module="{{ Str::slug($module_name) }}"
                                                                   {{ isset($role) && $roleswithPermissions->contains('permission_id', $permission->id) ? 'checked' : '' }}>
                                                            <label class="custom-control-label" 
                                                                   for="permission-{{ $permission->id }}">
                                                                <span class="badge badge-info badge-sm mr-1">
                                                                    <i class="fas fa-key"></i>
                                                                </span>
                                                                {{ ucfirst(str_replace('_', ' ', $permission->name)) }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas {{ isset($role) ? 'fa-save' : 'fa-plus-circle' }}"></i>
                                    {{ isset($role) ? 'Update Role' : 'Create Role' }}
                                </button>
                                <a href="{{ route('admin.user_roles.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Card Styling */
    .card-primary.card-outline {
        border-top: 3px solid #007bff;
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    /* Permission Cards */
    .card-outline.card-primary {
        border: 1px solid #dee2e6;
        transition: all 0.3s ease;
    }
    
    .card-outline.card-primary:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .card-header.py-2 {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    /* Custom Checkbox Styling */
    .custom-control-label {
        cursor: pointer;
        font-size: 13px;
    }
    
    .custom-control-label:hover {
        color: #007bff;
    }
    
    .custom-checkbox .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #007bff;
        border-color: #007bff;
    }
    
    /* Badge Styling */
    .badge-info {
        background-color: #e1f0fa;
        color: #0c5460;
    }
    
    /* Form Control Styling */
    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    
    /* Button Group Styling */
    .btn-group .btn {
        margin-right: 5px;
        border-radius: 4px !important;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border: none;
    }
    
    .btn-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
        border: none;
    }
    
    .btn-outline-primary {
        border-color: #007bff;
        color: #007bff;
    }
    
    .btn-outline-primary:hover {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
    }
    
    .btn-outline-secondary {
        border-color: #6c757d;
        color: #6c757d;
    }
    
    .btn-outline-secondary:hover {
        background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
        color: white;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .col-md-4 {
            margin-bottom: 15px;
        }
        
        .btn-group {
            display: flex;
            flex-direction: column;
        }
        
        .btn-group .btn {
            margin-bottom: 5px;
            width: 100%;
        }
    }
    
    /* Module Selection Indicator */
    .module-checkbox:checked + label {
        color: #007bff;
        font-weight: bold;
    }
    
    /* Permission Hover Effect */
    .custom-control {
        padding: 3px 0;
        border-radius: 3px;
        transition: background-color 0.2s;
    }
    
    .custom-control:hover {
        background-color: #f0f7ff;
    }
    
    /* Breadcrumb Styling */
    .breadcrumb {
        background: transparent;
        padding: 0;
        margin: 0;
    }
    
    .breadcrumb-item a {
        color: #007bff;
    }
    
    /* Loading State */
    .ajaxsubmit.loading {
        opacity: 0.7;
        pointer-events: none;
    }
</style>
@endpush

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Simple function to handle module checkbox changes
    const moduleCheckboxes = document.querySelectorAll('.module-checkbox');
    const selectAllBtn = document.getElementById('select-all-modules');

    // Function to check/uncheck all permissions in a module
    function togglePermissions(moduleCheckbox) {
        // Find the parent card container
        const card = moduleCheckbox.closest('.card');
        // Find all permission checkboxes within this card
        const permissions = card.querySelectorAll('.permission-checkbox');
        
        permissions.forEach(permission => {
            permission.checked = moduleCheckbox.checked;
        });
    }

    // Add event listeners to module checkboxes
    moduleCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            togglePermissions(this);
        });
    });

    // Handle Select All button
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            const allModuleCheckboxes = document.querySelectorAll('.module-checkbox');
            const allPermissionCheckboxes = document.querySelectorAll('.permission-checkbox');
            
            // Check if all modules are currently checked
            let allChecked = true;
            allModuleCheckboxes.forEach(cb => {
                if (!cb.checked) allChecked = false;
            });
            
            // Toggle based on current state
            allModuleCheckboxes.forEach(checkbox => {
                checkbox.checked = !allChecked;
                // Trigger the change event to update permissions
                const event = new Event('change', { bubbles: true });
                checkbox.dispatchEvent(event);
            });
            
            // Show simple alert (optional)
            if (!allChecked) {
                alert('All permissions selected');
            } else {
                alert('All permissions deselected');
            }
        });
    }

    // On page load, check which modules should be checked based on permissions
    // This handles the edit mode
    document.querySelectorAll('.card').forEach(card => {
        const moduleCheckbox = card.querySelector('.module-checkbox');
        const permissions = card.querySelectorAll('.permission-checkbox');
        const checkedPermissions = card.querySelectorAll('.permission-checkbox:checked');
        
        if (permissions.length === checkedPermissions.length && permissions.length > 0) {
            moduleCheckbox.checked = true;
        }
    });
});
</script>