@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Upload Trip Routes Prices</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">

    <div class="card card-primary card-outline">
        <div class="card-body">
            @include('layouts.admin_theme.alert')

            <form action="{{ route('admin.trip-routes-price.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>Select Excel File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                </div>
                <button type="submit" class="btn btn-primary">Upload</button>
            </form>

        </div>
    </div>

</div>
</section>
@endsection