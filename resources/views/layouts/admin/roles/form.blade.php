@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
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
                                    Role name should be unique and descriptive (e.g., Admin, Driver, Helper, Finance)
                                </small>
                            </div>
                        </div>
                    </div>

                    @php
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
                                    <div class="d-flex justify-content-between align-items-center" style="gap: 10px;">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="select-all-modules">
                                            <i class="fas fa-check-double"></i> Select/Deselect All
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                Select modules to grant all permissions or select individually
                                            </small>
                                            <span class="badge badge-info" id="selected-count">0 permissions selected</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="permissions-container" style="max-height: 500px; overflow-y: auto; padding-right: 10px;">
                                    <div class="row">
                                        @foreach ($groupedPermissions as $module_name => $permissionsGroup)
                                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                                                <div class="card card-outline card-primary h-100">
                                                    <div class="card-header py-2">
                                                        <div class="d-flex align-items-center">
                                                            <div class="custom-control custom-checkbox mr-2">
                                                                <input type="checkbox" 
                                                                       class="custom-control-input module-checkbox" 
                                                                       id="module-{{ Str::slug($module_name) }}">
                                                                <label class="custom-control-label font-weight-bold" 
                                                                       for="module-{{ Str::slug($module_name) }}">
                                                                    <i class="fas fa-cube"></i> 
                                                                    <span class="d-none d-sm-inline">{{ Str::limit($module_name, 20) }}</span>
                                                                    <span class="d-sm-none">{{ Str::limit($module_name, 10) }}</span>
                                                                </label>
                                                            </div>
                                                            <span class="badge badge-info ml-auto">
                                                                {{ $permissionsGroup->count() }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="card-body py-2" style="max-height: 200px; overflow-y: auto;">
                                                        @foreach ($permissionsGroup as $permission)
                                                            <div class="custom-control custom-checkbox mb-2">
                                                                <input type="checkbox" 
                                                                       class="custom-control-input permission-checkbox"
                                                                       name="permissions[]" 
                                                                       value="{{ $permission->id }}"
                                                                       id="permission-{{ $permission->id }}"
                                                                       data-module="{{ Str::slug($module_name) }}"
                                                                       {{ isset($role) && $roleswithPermissions->contains('permission_id', $permission->id) ? 'checked' : '' }}>
                                                                <label class="custom-control-label text-wrap" 
                                                                       for="permission-{{ $permission->id }}">
                                                                    <span class="badge badge-info badge-sm mr-1">
                                                                        <i class="fas fa-key"></i>
                                                                    </span>
                                                                    <span class="permission-text">{{ ucfirst(str_replace('_', ' ', $permission->name)) }}</span>
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
                    </div>

                    <!-- Form Actions -->
                <div class="row mt-3 mb-5">
                    <div class="col-12">
                        <div class="card-footer d-flex flex-wrap gap-2 justify-content-between align-items-center action-buttons" style="position: sticky; bottom: 0; background: #fff; padding: 10px; z-index: 10; border-top: 1px solid #dee2e6;">
                            <!-- Submit button -->
                            <button type="submit" class="btn btn-primary">
                                <i class="fas {{ isset($role) ? 'fa-save' : 'fa-plus-circle' }}"></i>
                                {{ isset($role) ? 'Update Role' : 'Create Role' }}
                            </button>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

<style>
    /* Make action buttons always visible */
.action-buttons {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 10px !important;
    justify-content: space-between !important;
    align-items: center !important;
}

/* Make buttons take full width on mobile */
@media (max-width: 576px) {
    .action-buttons .btn {
        width: 100% !important;
        min-width: auto !important;
        margin: 5px 0 !important;
    }
}

/* Ensure buttons have proper z-index and visibility */
.action-buttons .btn {
    visibility: visible !important;
    opacity: 1 !important;
    position: relative !important;
    z-index: 2 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const moduleCheckboxes = document.querySelectorAll('.module-checkbox');
    const selectAllBtn = document.getElementById('select-all-modules');
    const selectedCount = document.getElementById('selected-count');

    function updateSelectedCount() {
        const checked = document.querySelectorAll('.permission-checkbox:checked').length;
        selectedCount.textContent = `${checked} permissions selected`;
    }

    function togglePermissions(moduleCheckbox) {
        const card = moduleCheckbox.closest('.card');
        const permissions = card.querySelectorAll('.permission-checkbox');
        permissions.forEach(permission => permission.checked = moduleCheckbox.checked);
        updateSelectedCount();
    }

    moduleCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() { togglePermissions(this); });
    });

    document.querySelectorAll('.permission-checkbox').forEach(pc => {
        pc.addEventListener('change', updateSelectedCount);
    });

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            const allModuleCheckboxes = document.querySelectorAll('.module-checkbox');
            const allChecked = Array.from(allModuleCheckboxes).every(cb => cb.checked);

            allModuleCheckboxes.forEach(checkbox => {
                checkbox.checked = !allChecked;
                const event = new Event('change', { bubbles: true });
                checkbox.dispatchEvent(event);
            });

            alert(!allChecked ? 'All permissions selected' : 'All permissions deselected');
        });
    }

    // Initialize module checkboxes based on permission checkboxes
    document.querySelectorAll('.card').forEach(card => {
        const moduleCheckbox = card.querySelector('.module-checkbox');
        const permissions = card.querySelectorAll('.permission-checkbox');
        if (permissions.length && Array.from(permissions).every(p => p.checked)) {
            moduleCheckbox.checked = true;
        }
    });

    updateSelectedCount();
});
</script>