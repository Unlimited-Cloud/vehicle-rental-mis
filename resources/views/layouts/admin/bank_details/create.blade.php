@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">
        <h1>{{ isset($bankDetail) ? 'Edit Bank Detail' : 'Add Bank Detail' }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

<div class="card card-primary card-outline">

<form method="POST"
      action="{{ isset($bankDetail)
            ? route('admin.bank-details.update', [$crew->id, $bankDetail->id])
            : route('admin.bank-details.store', $crew->id) }}">

    @csrf

    @if(isset($bankDetail))
        @method('PUT')
    @endif

    <div class="card-body">

        @include('layouts.admin_theme.alert')

        <div class="row">

            <div class="col-md-6">
                <div class="form-group">
                    <label>Bank Name *</label>
                    <select name="bank_name" class="form-control" required>
                        <option value="">Select Bank</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank->bank_name }}"
                                    {{ old('bank_name', $bankDetail->bank_name ?? '') == $bank->bank_name ? 'selected' : '' }}>
                                {{ $bank->bank_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- <div class="col-md-6">
                <div class="form-group">
                    <label>Bank Code *</label>
                    <input type="text"
                           name="bank_code"
                           class="form-control"
                           value="{{ old('bank_code', $bankDetail->bank_code ?? '') }}"
                           required>
                </div>
            </div> --}}

            <div class="col-md-6">
                <div class="form-group">
                    <label>Account Holder Name *</label>
                    <input type="text"
                           name="account_holder_name"
                           class="form-control"
                           value="{{ old('account_holder_name', $bankDetail->account_holder_name ?? '') }}"
                           required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Account Number *</label>
                    <input type="text"
                           name="account_number"
                           class="form-control"
                           value="{{ old('account_number', $bankDetail->account_number ?? '') }}"
                           required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Status</label>

                    <div class="form-check mt-2">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               class="form-check-input"
                               id="is_active"
                               {{ old('is_active', $bankDetail->is_active ?? 1) ? 'checked' : '' }}>

                        <label class="form-check-label" for="is_active">
                            Set as Active
                        </label>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <div class="card-footer text-right">

        <button type="submit" class="btn btn-primary">
            {{ isset($bankDetail) ? 'Update Bank Detail' : 'Add Bank Detail' }}
        </button>

        <a href="{{ route('admin.bank-details.index', $crew->id) }}"
           class="btn btn-secondary">
            Back
        </a>

    </div>

</form>

</div>
</div>
</section>

@endsection