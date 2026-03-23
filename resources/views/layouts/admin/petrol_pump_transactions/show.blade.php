{{-- resources/views/layouts/admin/petrol_pump_transactions/show.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Transaction Details: {{ $petrolPumpTransaction->invoice_number }}</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-body">
                @include('layouts.admin_theme.alert')

                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">Invoice Number</th>
                                <td><strong>{{ $petrolPumpTransaction->invoice_number }}</strong></td>
                            </tr>
                            <tr>
                                <th>Transaction Date</th>
                                <td>{{ $petrolPumpTransaction->transaction_date->format('d-m-Y') }}</td>
                            </tr>
                            <tr>
                                <th>Petrol Pump</th>
                                <td>
                                    <a href="{{ route('admin.petrol_pumps.show', $petrolPumpTransaction->petrolPump->id) }}">
                                        {{ $petrolPumpTransaction->petrolPump->name }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Vehicle</th>
                                <td>{{ $petrolPumpTransaction->vehicle->vehicle_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Driver</th>
                                <td>{{ $petrolPumpTransaction->driver->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Transaction Type</th>
                                <td>{!! $petrolPumpTransaction->transaction_type_badge !!}</td>
                            </tr>
                            <tr>
                                <th>Amount</th>
                                <td>{{ $petrolPumpTransaction->formatted_amount }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">Paid Amount</th>
                                <td>{{ $petrolPumpTransaction->formatted_paid_amount ?? '₹ 0.00' }}</td>
                            </tr>
                            <tr>
                                <th>Balance</th>
                                <td>{{ $petrolPumpTransaction->formatted_balance ?? '₹ 0.00' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>{!! $petrolPumpTransaction->status_badge !!}</td>
                            </tr>
                            <tr>
                                <th>Payment Method</th>
                                <td>{!! $petrolPumpTransaction->payment_method_badge !!}</td>
                            </tr>
                            <tr>
                                <th>Reference Number</th>
                                <td>{{ $petrolPumpTransaction->reference_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Odometer Reading</th>
                                <td>{{ $petrolPumpTransaction->odometer_reading ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($petrolPumpTransaction->fuel_quantity)
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h4>Fuel Details</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">Fuel Quantity</th>
                                <td>{{ $petrolPumpTransaction->fuel_quantity }} Liters</td>
                            </tr>
                            <tr>
                                <th>Fuel Type</th>
                                <td>{!! $petrolPumpTransaction->fuel_type_badge !!}</td>
                            </tr>
                            <tr>
                                <th>Rate Per Liter</th>
                                <td>₹ {{ number_format($petrolPumpTransaction->rate_per_liter, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Total Fuel Amount</th>
                                <td>₹ {{ number_format($petrolPumpTransaction->fuel_quantity * $petrolPumpTransaction->rate_per_liter, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                @endif

                {{-- Image Gallery Section --}}
                @php
                    $images = [
                        'pump_before' => ['label' => 'Pump Reading (Before)', 'icon' => 'fa-camera', 'color' => 'primary'],
                        'pump_after' => ['label' => 'Pump Reading (After)', 'icon' => 'fa-camera', 'color' => 'success'],
                        'tank_before' => ['label' => 'Tank Reading (Before)', 'icon' => 'fa-gas-pump', 'color' => 'warning'],
                        'tank_after' => ['label' => 'Tank Reading (After)', 'icon' => 'fa-gas-pump', 'color' => 'info'],
                    ];
                    $hasImages = false;
                    foreach ($images as $field => $details) {
                        if (!empty($petrolPumpTransaction->$field)) {
                            $hasImages = true;
                            break;
                        }
                    }
                @endphp

                @if($hasImages)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h4><i class="fas fa-images"></i> Transaction Images</h4>
                        <hr>
                        <div class="row">
                            @foreach($images as $field => $details)
                                @if(!empty($petrolPumpTransaction->$field))
                                    @php
                                        $imagePath = asset($petrolPumpTransaction->$field);
                                        $imageUrl = $petrolPumpTransaction->$field;
                                    @endphp
                                    <div class="col-md-6 col-lg-3 mb-4">
                                        <div class="card h-100 shadow-sm">
                                            <div class="card-header bg-{{ $details['color'] }} text-white">
                                                <i class="fas {{ $details['icon'] }}"></i>
                                                {{ $details['label'] }}
                                            </div>
                                            <div class="card-body p-2 text-center">
                                                <a href="{{ $imagePath }}" data-toggle="lightbox" data-gallery="transaction-gallery" data-title="{{ $details['label'] }}">
                                                    <img src="{{ $imagePath }}" 
                                                         alt="{{ $details['label'] }}" 
                                                         class="img-fluid rounded"
                                                         style="max-height: 200px; object-fit: cover; cursor: pointer;">
                                                </a>
                                            </div>
                                            
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                @if($petrolPumpTransaction->remarks)
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h4><i class="fas fa-comment"></i> Remarks</h4>
                        <div class="card">
                            <div class="card-body">
                                {{ $petrolPumpTransaction->remarks }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="row mt-4">
                    <div class="col-md-12">
                        <h4><i class="fas fa-info-circle"></i> Additional Information</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">Created At</th>
                                <td>{{ $petrolPumpTransaction->created_at ? $petrolPumpTransaction->created_at->format('d-m-Y H:i:s') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Last Updated</th>
                                <td>{{ $petrolPumpTransaction->updated_at ? $petrolPumpTransaction->updated_at->format('d-m-Y H:i:s') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Created By</th>
                                <td>{{ $petrolPumpTransaction->creator->name ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="text-right mt-3">
                    <a href="{{ route('admin.petrol_pump_transactions.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('admin.petrol_pump_transactions.edit', $petrolPumpTransaction->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Transaction
                    </a>
                    <form action="{{ route('admin.petrol_pump_transactions.destroy', $petrolPumpTransaction->id) }}"
                          method="POST"
                          style="display:inline-block;"
                          onsubmit="return confirm('Are you sure you want to delete this transaction?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm bg-red">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</section>
@endsection

@push('styles')
<style>
    .view-image {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .view-image:hover {
        transform: scale(1.05);
    }
    .card {
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
    }
    img {
        transition: transform 0.3s ease;
    }
    img:hover {
        transform: scale(1.05);
    }
</style>
@endpush

@push('scripts')
<!-- Include Lightbox2 or Bootstrap Lightbox -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize lightbox for gallery
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox({
            alwaysShowClose: true,
            wrapping: true,
            onShow: function() {
                console.log('Lightbox opened');
            }
        });
    });

    // Full size image viewer
    $('.view-image').click(function() {
        var imageUrl = $(this).data('image');
        var title = $(this).data('title');
        
        // Create modal for full size image
        var modalHtml = `
            <div class="modal fade" id="imageModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="${imageUrl}" class="img-fluid" alt="${title}">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <a href="${imageUrl}" class="btn btn-primary" download>
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        $('#imageModal').remove();
        
        // Append modal to body
        $('body').append(modalHtml);
        
        // Show modal
        $('#imageModal').modal('show');
        
        // Remove modal on hide
        $('#imageModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    });
});
</script>
@endpush