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
                                <th>Vehicle </th>
                                <td>
                                        {{ $petrolPumpTransaction->vehicle->vehicle_name }}
                                </td>
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
                            {{-- <tr>
                                <th style="width: 200px;">Paid Amount</th>
                                <td>{{ $petrolPumpTransaction->formatted_paid_amount }}</td>
                            </tr>
                            <tr>
                                <th>Balance</th>
                                <td>{{ $petrolPumpTransaction->formatted_balance }}</td>
                            </tr> --}}
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

                @if($petrolPumpTransaction->remarks)
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h4>Remarks</h4>
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
                        <h4>Additional Information</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">Created At</th>
                                <td>{{ $petrolPumpTransaction->created_at ? $petrolPumpTransaction->created_at->format('d-m-Y H:i:s') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Last Updated</th>
                                <td>{{ $petrolPumpTransaction->updated_at ? $petrolPumpTransaction->updated_at->format('d-m-Y H:i:s') : 'N/A' }}</td>
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