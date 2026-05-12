@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>FAQs</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.faq.create') }}"
       class="btn btn-sm btn-primary">

        <i class="fa fa-plus"></i> Add FAQ
    </a>
</div>

<div class="table-responsive">

<table id="dataTable"
       class="table table-bordered table-striped show-search-bar">

<thead>
<tr>
    <th>SN</th>
    <th>Question</th>
    <th>Answer</th>
    <th>Sort Order</th>
    <th>Status</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>

@foreach($faqs as $faq)

<tr>

<td>{{ $loop->iteration }}</td>

<td>{{ $faq->question }}</td>

<td>
    {{ \Illuminate\Support\Str::limit(strip_tags($faq->answer), 80) }}
</td>

<td>{{ $faq->sort_order }}</td>

<td>
    @if($faq->is_active)
        <span class="badge bg-success">Active</span>
    @else
        <span class="badge bg-danger">Inactive</span>
    @endif
</td>

<td>

<a href="{{ route('admin.faq.edit', $faq->id) }}"
   class="btn btn-primary btn-sm">

    <i class="fas fa-edit"></i>
</a>

<form action="{{ route('admin.faq.destroy', $faq->id) }}"
      method="POST"
      style="display:inline-block;"
      onsubmit="return confirm('Delete this FAQ?');">

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
    $('#dataTable').DataTable();
});
</script>
@endpush