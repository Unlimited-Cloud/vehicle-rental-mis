@extends('layouts.admin_theme.container')

@section('dynamicdata')

<div class="content-header">
    <div class="container-fluid">

        <h1>
            {{ isset($price) ? 'Edit Vehicle Route Price' : 'Create Vehicle Route Price' }}
        </h1>

    </div>
</div>

<section class="content">

<div class="container-fluid">

<form
    action="{{ isset($price)
        ? route('admin.trip-routes-vehicle-prices.update',$price->id)
        : route('admin.trip-routes-vehicle-prices.store') }}"
    method="POST">

    @csrf

    @if(isset($price))
        @method('PUT')
    @endif

    @include('layouts.admin_theme.alert')

    <div class="card">

        <div class="card-body">

            <div class="row">

               <div class="col-md-6">

    <label>Trip Category</label>

    <select
        id="trip_category_id"
        class="form-control">

        <option value="">
            Select Category
        </option>

        @foreach($categories as $id => $name)

            <option value="{{ $id }}">
                {{ $name }}
            </option>

        @endforeach

    </select>

</div>

<div class="col-md-6">

    <label>Trip Route</label>

    <select
        id="trip_route_id"
        name="trip_route_id"
        class="form-control"
        required>

        <option value="">
            Select Route
        </option>

    </select>

</div>

                <div class="col-md-6">

                    <label>Vehicle</label>

                    <select
                        name="vehicle_id"
                        class="form-control"
                        required>

                        <option value="">
                            Select Vehicle
                        </option>

                        @foreach($vehicles as $id => $name)

                            <option
                                value="{{ $id }}"
                                {{ old('vehicle_id',$price->vehicle_id ?? '') == $id ? 'selected' : '' }}>

                                {{ $name }}

                            </option>

                        @endforeach

                    </select>

                </div>

                <div class="col-md-4 mt-3">

                    <label>Price</label>

                    <input
                        type="number"
                        step="0.01"
                        name="price"
                        class="form-control"
                        value="{{ old('price',$price->price ?? '') }}"
                        required>

                </div>

            </div>

        </div>

        <div class="card-footer text-right">

            <a href="{{ route('admin.trip-routes-vehicle-prices.index') }}"
               class="btn btn-secondary">
                Back
            </a>

            <button class="btn btn-primary">

                {{ isset($price) ? 'Update Price' : 'Save Price' }}

            </button>

        </div>

    </div>

</form>

</div>

</section>


{{-- @push('scripts') --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){

    var selectedRoute = $('#selected_route_id').val();

    $('#trip_category_id').change(function(){

        var categoryId = $(this).val();

        $('#trip_route_id')
            .html('<option value="">Loading...</option>');

        if(categoryId){

            $.ajax({

                url: '/dashboard/get-trip-routes/' + categoryId,

                type: 'GET',

                success: function(routes){

                    let options =
                        '<option value="">Select Route</option>';

                    $.each(routes,function(index,route){

                        options +=
                            '<option value="' +
                            route.id +
                            '">' +
                            route.title +
                            '</option>';

                    });

                    $('#trip_route_id').html(options);

                    if(selectedRoute){

                        $('#trip_route_id')
                            .val(selectedRoute);

                    }

                },

                error:function(){

                    $('#trip_route_id').html(
                        '<option value="">Error loading routes</option>'
                    );

                }

            });

        }else{

            $('#trip_route_id').html(
                '<option value="">Select Route</option>'
            );

        }

    });

});
</script>
{{-- @endpush --}}
@endsection
