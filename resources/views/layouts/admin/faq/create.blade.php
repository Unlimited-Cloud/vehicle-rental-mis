@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($faq) ? 'Edit FAQ' : 'Add FAQ' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline">

<form action="{{ isset($faq) ? route('admin.faq.update',$faq->id) : route('admin.faq.store') }}"
      method="POST">

@csrf
@if(isset($faq))
    @method('PUT')
@endif

<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="row">

<div class="col-md-12">
<div class="form-group">
<label>Question *</label>

<input type="text"
       name="question"
       class="form-control"
       value="{{ old('question',$faq->question ?? '') }}"
       required>
</div>
</div>

<div class="col-md-12">
<div class="form-group">
<label>Answer *</label>

<textarea name="answer"
          class="form-control"
          rows="5"
          required>{{ old('answer',$faq->answer ?? '') }}</textarea>
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Sort Order</label>

<input type="number"
       name="sort_order"
       class="form-control"
       value="{{ old('sort_order',$faq->sort_order ?? 0) }}">
</div>
</div>

<div class="col-md-6">
<div class="form-group">
<label>Status</label>

<select name="is_active" class="form-control">
    <option value="1"
        {{ old('is_active',$faq->is_active ?? 1) == 1 ? 'selected' : '' }}>
        Active
    </option>

    <option value="0"
        {{ old('is_active',$faq->is_active ?? 1) == 0 ? 'selected' : '' }}>
        Inactive
    </option>
</select>
</div>
</div>

</div>
</div>

<div class="card-footer text-right">
<button type="submit" class="btn btn-primary">
    {{ isset($faq) ? 'Update FAQ' : 'Add FAQ' }}
</button>
</div>

</form>

</div>
</div>
</section>
@endsection