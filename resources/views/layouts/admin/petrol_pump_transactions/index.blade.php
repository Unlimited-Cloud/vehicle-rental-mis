{{-- resources/views/layouts/admin/petrol_pump_transactions/index.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <h1>Petrol Pump Transactions</h1>
    </div>
</div>

<section class="content">
<div class="container-fluid">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

@include('layouts.admin_theme.alert')

<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('admin.petrol_pump_transactions.create') }}" class="btn btn-sm btn-primary">
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
                <th>Petrol Pump</th>
                <th>Type</th>
                <th>Amount</th>
                {{-- <th>Paid</th>
                <th>Balance</th> --}}
                <th>Fuel</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $transaction->invoice_number }}</td>
                <td>{{ $transaction->transaction_date->format('d-m-Y') }}</td>
                <td>
                    <a href="{{ route('admin.petrol_pumps.show', $transaction->petrolPump->id) }}">
                        {{ $transaction->petrolPump->name }}
                    </a>
                </td>
                <td>{!! $transaction->transaction_type_badge !!}</td>
                <td>{{ $transaction->formatted_amount }}</td>
                {{-- <td>{{ $transaction->formatted_paid_amount }}</td>
                <td>{{ $transaction->formatted_balance }}</td> --}}
                <td>
                    @if($transaction->fuel_quantity)
                        {{ $transaction->fuel_quantity }} L<br>
                        {!! $transaction->fuel_type_badge !!}
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

                    <form action="{{ route('admin.petrol_pump_transactions.destroy', $transaction->id) }}"
                          method="POST"
                          style="display:inline-block;"
                          onsubmit="return confirm('Are you sure you want to delete this transaction?');">
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