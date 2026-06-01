{{-- resources/views/layouts/admin/payments/show.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Payment Details</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Payments</a></li>
                    <li class="breadcrumb-item active">Payment #{{ $payment->id }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header {{ $payment->direction == 'in' ? 'bg-success' : 'bg-danger' }} text-white">
                    <h3 class="card-title">
                        <i class="fas {{ $payment->direction == 'in' ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                        {{ ucfirst($payment->direction) }} Payment - {{ ucfirst($payment->payment_method) }}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Payment ID:</th>
                                    <td>{{ $payment->unique_id }}</td>
                                </tr>
                                <tr>
                                    <th>Date:</th>
                                    <td>{{ $payment->payment_date->format('F d, Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Amount:</th>
                                    <td><h4 class="text-{{ $payment->direction == 'in' ? 'success' : 'danger' }}">रु {{ number_format($payment->amount, 2) }}</h4></td>
                                </tr>
                                <tr>
                                    <th>Direction:</th>
                                    <td>{!! $payment->direction_badge !!}</td>
                                </tr>
                                <tr>
                                    <th>Method:</th>
                                    <td>{!! $payment->payment_method_badge !!}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>{!! $payment->status_badge !!}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Transaction Ref:</th>
                                    <td>{{ $payment->transaction_reference ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Booking ID:</th>
                                    <td>
                                        @if($payment->vehicle_booking_id)
                                            <a href="{{ route('admin.vehicle_bookings.show', $payment->vehicle_booking_id) }}">
                                                #{{ $payment->vehicle_booking_id }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Customer:</th>
                                    <td>{{ $payment->vehicleBooking?->customer?->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created By:</th>
                                    <td>{{ $payment->creator?->name ?? 'System' }}</td>
                                </tr>
    
                            </table>
                        </div>
                                                    <tr>
    <th>Notes:</th>
    <td>
        <div class="card card-outline card-secondary mb-0">
            <div class="card-body p-2"
                 style="max-height: 200px; overflow-y: auto; white-space: pre-wrap;">
                {{ $payment->notes ?: 'No notes' }}
            </div>
        </div>
    </td>
</tr>
                    </div>
                    
                    @if($payment->proof)
                    <div class="mt-3">
                        <h5>Payment Proof</h5>
                        <hr>
                        @if(pathinfo($payment->proof, PATHINFO_EXTENSION) == 'pdf')
                            <a href="{{ asset('storage/' . $payment->proof) }}" target="_blank" class="btn btn-info">
                                <i class="fas fa-file-pdf"></i> View PDF
                            </a>
                        @else
                            <img src="{{ asset('storage/' . $payment->proof) }}" alt="Payment Proof" class="img-fluid img-thumbnail" style="max-height: 300px;">
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-secondary">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-info btn-block mb-2">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    {{-- <a href="{{ route('admin.payments.edit', $payment->id) }}" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-edit"></i> Edit Payment
                    </a> --}}
                          <form action="{{ route('admin.payments.destroy', [
            'method' => $payment->payment_method,
            'id' => $payment->id
        ]) }}"
      method="POST"
      style="display:inline-block;"
      onsubmit="return confirm('Are you sure?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm">
        <i class="fas fa-trash"></i>
    </button>
</form>
                </div>
            </div>
        </div>
    </div>
</div>
</section>
@endsection