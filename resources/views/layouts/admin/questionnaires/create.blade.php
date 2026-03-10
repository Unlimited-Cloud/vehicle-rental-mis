@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
<div class="container-fluid">
<h1>{{ isset($questionnaire) ? 'Edit Questionnaire' : 'Add Questionnaire' }}</h1>
</div>
</div>

<section class="content">
<div class="container-fluid">

<div class="card card-primary card-outline">

<form action="{{ isset($questionnaire) ? route('admin.questionnaires.update',$questionnaire->id) : route('admin.questionnaires.store') }}"
method="POST">

@csrf
@if(isset($questionnaire)) @method('PUT') @endif

<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="row">

<div class="col-md-12">
<div class="form-group">
<label>Question *</label>
<input type="text" name="question" class="form-control"
value="{{ old('question',$questionnaire->question ?? '') }}">
</div>
</div>

<div class="col-md-4">
<div class="form-group">
<label>Type *</label>
<select name="type" class="form-control">

<option value="text"
{{ old('type',$questionnaire->type ?? '')=='text'?'selected':'' }}>
Text
</option>

<option value="yes_no"
{{ old('type',$questionnaire->type ?? '')=='yes_no'?'selected':'' }}>
Yes / No
</option>

<option value="number"
{{ old('type',$questionnaire->type ?? '')=='number'?'selected':'' }}>
Number
</option>

<option value="checkbox"
{{ old('type',$questionnaire->type ?? '')=='checkbox'?'selected':'' }}>
Checkbox
</option>

</select>
</div>
</div>

<div class="col-md-4">
<div class="form-group">
<label>Sort Order</label>
<input type="number" name="sort_order" class="form-control"
value="{{ old('sort_order',$questionnaire->sort_order ?? 0) }}">
</div>
</div>

<div class="col-md-4">
<div class="form-group">
<label>Required</label>

<div class="form-check">
<input type="checkbox" name="is_required" value="1"
class="form-check-input"
{{ old('is_required',$questionnaire->is_required ?? 0) ? 'checked':'' }}>
<label class="form-check-label">Yes</label>
</div>

</div>
</div>

<div class="col-md-4">
<div class="form-group">
<label>Status</label>

<select name="is_active" class="form-control">
<option value="1"
{{ old('is_active',$questionnaire->is_active ?? 1)==1?'selected':'' }}>
Active
</option>
<option value="0"
{{ old('is_active',$questionnaire->is_active ?? 1)==0?'selected':'' }}>
Inactive
</option>
</select>

</div>
</div>

</div>
</div>

<div class="card-footer text-right">
<button type="submit" class="btn btn-primary">
{{ isset($questionnaire) ? 'Update Question' : 'Add Question' }}
</button>
</div>

</form>

</div>
</div>
</section>

@endsection