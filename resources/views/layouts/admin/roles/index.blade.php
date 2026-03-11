@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>User Lists</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">


         <div class="bg-white flex-col ">
            <table class="min-w-full table-auto w-full">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="text-gray-400 text-xs px-2 py-2 text-left whitespace-nowrap">Sn</th>
                        <th class="text-gray-400 text-xs px-2 py-2 text-left whitespace-nowrap">Name</th>
                        <th class="text-gray-400 text-xs px-2 text-left whitespace-nowrap">Created At</th>
                        <th class="text-gray-400 text-xs px-2 text-left whitespace-nowrap">Action</th>
                    </tr>
                </thead>

                <tbody class="tbodycontent bg-white ">

                    @forelse ($roles as $key=> $role)


                        <tr class="border">
                            <td class="px-2 text-left text-xs py-2">
                              {{$key+1}}
                            </td>
                            <td class="px-2 text-left text-xs py-2 ">
                                {{$role->name}}
                            </td>
                            <td class="px-2 text-left text-xs py-2 ">
                                {{$role->created_at}}
                            </td>

                            <td class="px-2 text-center text-xs py-2">
                                @if(auth()->user()->can('update_roles'))
                                <div class="flex">
                                    <a href="{{ route('admin.user_roles.edit', $role->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        @endforelse

                </tbody>
            </table>

            </div>
</div>
</div>
</div>
</div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true
    });
});
</script>
@endpush
