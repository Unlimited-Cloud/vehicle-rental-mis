@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1>
            {{ isset($role) ? 'Edit role' : 'Add role' }}
        </h1>

        <a href="{{ route('admin.user_roles.index') }}"
           class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<section class="content">
<div class="container-fluid">
@include('layouts.admin_theme.alert')

<div class="card card-primary card-outline">
    <div class="bg-white container rounded-md p-4"></div>
        <form 
         action="{{ isset($role) 
         ? route('admin.user_roles.update', $role->id) 
         : route('admin.user_roles.store') }}" method="POST" class="ajaxsubmit" >
            @csrf
            @if(isset($role))
            @method('PUT') <!-- Use PUT for updates -->
            @endif

            <div class="flex space-x-4 w-full">
                <div class="w-1/2">
                    <input 
                    name="name" 
                    class="w-full"
                    value="{{ old('name',$role->name ?? '') }}"
                    readonly
                     />
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-4">
            <button type="submit" class="btn btn-primary">
        {{ isset($role) ? 'Update Role' : 'Add Role' }}
    </button>

            </div>

            @php
            // Group permissions by module_id
            $groupedPermissions = $permissions->groupBy('module_name');
            @endphp

            <div class="container mx-auto mt-4">
                <div class="flex flex-wrap mt-3 -mx-3">
                    <!-- "Select All Modules" Checkbox -->
                    <div class="w-full mb-3 px-3">
                        <div class="flex items-center">
                            <input type="checkbox" id="select-all-modules" class="form-check-input">
                            <label class="font-bold text-blue-500 ml-2" for="select-all-modules">
                                Select All Modules
                            </label>
                        </div>
                    </div>
                    
                    @foreach ($groupedPermissions as $module_name => $permissionsGroup)
                    
                    <div class="w-full sm:w-1/2 md:w-1/3  text-bs">
                        <div class="bg-slate-100 shadow rounded-t rounded-lg w-fit module-container">
                            <div class="flex items-center bg-indigo-900 p-2 rounded text-black">
                                <input type="checkbox" class="form-check-input module-checkbox" id="module-{{ $module_name }}">
                                <label class=" ml-2 opacity-100 font-semibold" for="module-{{ $module_name }}">
                                    {{ $module_name }}
                                </label>
                            </div>

                            @foreach ($permissionsGroup as $permission)
                         
                            <div data-val="{{$permission->name}}" class="flex items-center ml-6 mt-2 p-2 border-b pb-1 space-x-1 hover:rounded-md hover:bg-slate-300">
                                <div class="flex items-center  text-yellow">
                                    <input type="checkbox" class="form-check-input permission-checkbox"
                                        name="permissions[]" value="{{ $permission->id }}"
                                        id="permission-{{ $permission->id }}"
                                        {{ isset($role) && $roleswithPermissions->contains('permission_id', $permission->id) ? 'checked' : '' }}>
                                    <label class="ml-2 italic text-sm" for="permission-{{ $permission->id }}">
                             {{ucfirst($permission->name)}}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </form>
        </div>
    </div>
</div>
</section>

@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const moduleCheckboxes = document.querySelectorAll('.module-checkbox');
        const selectAllCheckbox = document.getElementById('select-all-modules');

        function updatePermissions(moduleCheckbox) {
            const permissions = moduleCheckbox.closest('.module-container').querySelectorAll('.permission-checkbox');
            permissions.forEach(permission => {
                permission.checked = moduleCheckbox.checked;
            });
        }

        moduleCheckboxes.forEach(moduleCheckbox => {
            moduleCheckbox.addEventListener('change', function() {
                updatePermissions(this);
            });
        });

        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            moduleCheckboxes.forEach(moduleCheckbox => {
                moduleCheckbox.checked = isChecked;
                updatePermissions(moduleCheckbox);
            });
        });
    });
</script>