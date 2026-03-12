@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="m-0">User Roles</h1>
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">User Roles</li>
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
                    <i class="fas fa-user-tag"></i> Role Management
                </h3>
                {{-- @if(auth()->user()->can('create_roles'))
                <div class="card-tools">
                    <a href="{{ route('admin.user_roles.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus-circle"></i> Add New Role
                    </a>
                </div>
                @endif --}}
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table id="rolesTable" class="table table-bordered table-striped table-hover">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th width="50">SN</th>
                                <th>Role Name</th>
                                <th>Permissions</th>
                                <th>Created At</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($roles as $key => $role)
                            <tr>
                                <td>
                                    <span class="font-weight-bold">{{ $key + 1 }}</span>
                                </td>
                                
                                <td>
                                    <div class="d-flex align-items-center">
                                        {{-- <div class="avatar-circle bg-info mr-2">
                                            <span class="initials">{{ strtoupper(substr($role->name, 0, 2)) }}</span>
                                        </div> --}}
                                        <div>
                                            <strong>{{ $role->name }}</strong>
                                            @if($role->name == 'Admin')
                                                <span class="badge badge-danger ml-2">Super Admin</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                
                                <td>
                                    @if($role->permissions->count() > 0)
                                        <div class="permission-container">
                                            @foreach($role->permissions->take(3) as $permission)
                                                <span class="badge badge-info mb-1">
                                                    <i class="fas fa-check-circle"></i> {{ $permission->name }}
                                                </span>
                                            @endforeach
                                            @if($role->permissions->count() > 3)
                                                <span class="badge badge-secondary" data-toggle="tooltip" 
                                                      title="{{ $role->permissions->slice(3)->pluck('name')->implode(', ') }}">
                                                    <i class="fas fa-plus-circle"></i> +{{ $role->permissions->count() - 3 }} more
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">
                                            <i class="fas fa-times-circle"></i> No permissions
                                        </span>
                                    @endif
                                </td>
                                
                                {{-- <td>
                                    <span class="badge badge-primary badge-lg">
                                        <i class="fas fa-users"></i> {{ $role->users_count ?? 0 }} Users
                                    </span>
                                </td> --}}
                                
                                <td>
                                    <div>
                                        <i class="far fa-calendar-alt text-primary"></i> 
                                        {{ $role->created_at->format('M d, Y') }}
                                    </div>
                                    <small class="text-muted">
                                        <i class="far fa-clock"></i> 
                                        {{ $role->created_at->format('h:i A') }}
                                    </small>
                                </td>
                                
                                <td>
                                    <div class="btn-group">
                                        @if(auth()->user()->can('view_roles'))
                                        <a href="{{ route('admin.user_roles.show', $role->id) }}" 
                                           class="btn btn-sm btn-info" 
                                           data-toggle="tooltip" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endif
                                        
                                        @if(auth()->user()->can('update_roles'))
                                        <a href="{{ route('admin.user_roles.edit', $role->id) }}" 
                                           class="btn btn-sm btn-primary" 
                                           data-toggle="tooltip" 
                                           title="Edit Role">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                        
                                        @if(auth()->user()->can('delete_roles') && $role->name != 'Admin')
                                        <button type="button" 
                                                class="btn btn-sm btn-danger delete-btn" 
                                                data-id="{{ $role->id }}"
                                                data-name="{{ $role->name }}"
                                                data-toggle="tooltip" 
                                                title="Delete Role">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-user-tag fa-3x text-muted mb-3"></i>
                                        <h5>No Roles Found</h5>
                                        <p class="text-muted">Start by creating a new role</p>
                                        @if(auth()->user()->can('create_roles'))
                                        <a href="{{ route('admin.user_roles.create') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus-circle"></i> Add New Role
                                        </a>
                                        @endif
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
</section>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<style>
    /* Card styling */
    .card-primary.card-outline {
        border-top: 3px solid #007bff;
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .card-title {
        font-weight: 600;
        color: #333;
    }
    
    /* Avatar styling */
    .avatar-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .avatar-circle.bg-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }
    
    .initials {
        color: white;
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
    }
    
    /* Badge styling */
    .badge {
        font-size: 11px;
        padding: 5px 8px;
        margin: 2px;
        font-weight: 500;
    }
    
    .badge-info {
        background-color: #e1f0fa;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
    
    .badge-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }
    
    .badge-primary.badge-lg {
        font-size: 12px;
        padding: 6px 10px;
    }
    
    /* Permission container */
    .permission-container {
        max-width: 250px;
    }
    
    /* Table styling */
    .table td {
        vertical-align: middle;
        padding: 12px 8px;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f5f9ff;
    }
    
    /* Button group styling */
    .btn-group .btn {
        margin: 0 2px;
        border-radius: 4px !important;
        transition: all 0.3s ease;
    }
    
    .btn-group .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .btn-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        border: none;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border: none;
    }
    
    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
        border: none;
    }
    
    /* Empty state styling */
    .empty-state {
        padding: 40px 20px;
        text-align: center;
    }
    
    .empty-state i {
        color: #dee2e6;
    }
    
    /* Card tools */
    .card-tools {
        float: right;
    }
    
    /* Breadcrumb styling */
    .breadcrumb {
        background: transparent;
        padding: 0;
        margin: 0;
    }
    
    .breadcrumb-item a {
        color: #007bff;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .avatar-circle {
            width: 30px;
            height: 30px;
        }
        
        .initials {
            font-size: 12px;
        }
        
        .btn-group .btn {
            padding: 4px 8px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#rolesTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "pageLength": 10,
        "order": [[0, 'asc']],
        "language": {
            "search": "Search Roles:",
            "lengthMenu": "Show _MENU_ roles per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ roles",
            "infoEmpty": "Showing 0 to 0 of 0 roles",
            "infoFiltered": "(filtered from _MAX_ total roles)"
        }
    });

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Handle delete with confirmation
    $('.delete-btn').on('click', function() {
        let id = $(this).data('id');
        let name = $(this).data('name');
        
        Swal.fire({
            title: 'Delete Role?',
            html: `Are you sure you want to delete <strong>${name}</strong>?<br>
                   <small class="text-danger">This action cannot be undone!</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Create and submit form
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.user_roles.index") }}/' + id;
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush