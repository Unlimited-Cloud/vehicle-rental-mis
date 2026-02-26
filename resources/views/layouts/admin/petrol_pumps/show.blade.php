{{-- resources/views/layouts/admin/petrol_pumps/show.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Petrol Pump Details: {{ $petrolPump->name }}</h1>
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
                                <th style="width: 200px;">Pump Name</th>
                                <td>{{ $petrolPump->name }}</td>
                            </tr>
                            <tr>
                                <th>Owner Name</th>
                                <td>{{ $petrolPump->owner_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td>{{ $petrolPump->phone }}</td>
                            </tr>
                            <tr>
                                <th>Alternate Phone</th>
                                <td>{{ $petrolPump->alternate_phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $petrolPump->email ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td>{{ $petrolPump->address ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">City</th>
                                <td>{{ $petrolPump->city ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>State</th>
                                <td>{{ $petrolPump->state ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Pincode</th>
                                <td>{{ $petrolPump->pincode ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>GST Number</th>
                                <td>{{ $petrolPump->gst_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>PAN Number</th>
                                <td>{{ $petrolPump->pan_number ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-12">
                        <h4 class="mt-3">Financial Details</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">Opening Balance</th>
                                <td>{{ $petrolPump->formatted_opening_balance }}</td>
                            </tr>
                            <tr>
                                <th>Current Balance</th>
                                <td>
                                    {{ $petrolPump->formatted_current_balance }}
                                    {!! $petrolPump->balance_type_badge !!}
                                </td>
                            </tr>
                            <tr>
                                <th>Balance Status</th>
                                <td>{{ $petrolPump->balance_with_indicator }}</td>
                            </tr>
                            <tr>
                                <th>Credit Limit</th>
                                <td>{{ $petrolPump->formatted_credit_limit }}</td>
                            </tr>
                            <tr>
                                <th>Credit Limit Status</th>
                                <td>
                                    @if($petrolPump->is_credit_limit_exceeded)
                                        <span class="badge badge-danger">Exceeded</span>
                                    @else
                                        <span class="badge badge-success">Within Limit</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>{!! $petrolPump->status_badge !!}</td>
                            </tr>
                            <tr>
                                <th>Remarks</th>
                                <td>{{ $petrolPump->remarks ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <h4 class="mt-4">Transaction History</h4>
                <div class="text-right mb-3">
                    <a href="{{ route('admin.petrol_pump_transactions.create', ['petrol_pump_id' => $petrolPump->id]) }}" class="btn btn-sm btn-primary">
                        <i class="fa fa-plus"></i> Add Transaction
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Invoice No.</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Fuel</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $transaction->invoice_number }}</td>
                                <td>{{ $transaction->transaction_date->format('d-m-Y') }}</td>
                                <td>{!! $transaction->transaction_type_badge !!}</td>
                                <td>{{ $transaction->formatted_amount }}</td>
                                <td>{{ $transaction->formatted_paid_amount }}</td>
                                <td>{{ $transaction->formatted_balance }}</td>
                                <td>
                                    @if($transaction->fuel_quantity)
                                        {{ $transaction->fuel_quantity }} L<br>
                                        {!! $transaction->fuel_type_badge !!}<br>
                                        @if($transaction->rate_per_liter)
                                            @ {{ $transaction->rate_per_liter }}/L
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{!! $transaction->payment_method_badge !!}</td>
                                <td>{!! $transaction->status_badge !!}</td>
                                <td>
                                    <a href="{{ route('admin.petrol_pump_transactions.edit', $transaction->id) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.petrol_pump_transactions.show', $transaction->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center">No transactions found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-right mt-3">
                    <a href="{{ route('admin.petrol_pumps.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('admin.petrol_pumps.edit', $petrolPump->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Pump
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</section>
@endsection