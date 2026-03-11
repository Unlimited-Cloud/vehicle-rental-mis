@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Questionnaires</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.questionnaires.create') }}" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Add Question
    </a>
</div>

<div class="table-responsive">
<table id="dataTable" class="table table-bordered table-striped show-search-bar">

<thead>
<tr>
    <th>SN</th>
    <th>Question</th>
    <th>Type</th>
    <th>Required</th>
    <th>Status</th>
    <th>Sort Order</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>

@foreach($questionnaires as $q)

<tr>

<td>{{ $loop->iteration }}</td>

<td>{{ $q->question }}</td>

<td>{{ ucfirst($q->type) }}</td>

<td>
    <span class="badge badge-{{ $q->is_required ? 'success' : 'secondary' }}">
        {{ $q->is_required ? 'Yes' : 'No' }}
    </span>
</td>

<td>
    <span class="badge badge-{{ $q->is_active ? 'success' : 'danger' }}">
        {{ $q->is_active ? 'Active' : 'Inactive' }}
    </span>
</td>

<td>{{ $q->sort_order }}</td>

<td>

<a href="{{ route('admin.questionnaires.edit', $q->id) }}" class="btn btn-primary btn-sm">
    <i class="fas fa-edit"></i>
</a>

<form action="{{ route('admin.questionnaires.destroy', $q->id) }}"
      method="POST"
      style="display:inline-block;"
      onsubmit="return confirm('Are you sure you want to delete this question?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm bg-red">
        <i class="fa fa-trash"></i>
    </button>
</form>

</td>

</tr>

@endforeach

</tbody>
</table>
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
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true
    });

});
</script>
@endpush