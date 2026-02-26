{{-- resources/views/layouts/admin/petrol_pump_transactions/index.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Petrol Pump Transactions</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline card-tabs">
                <div class="card-body">

                    @include('layouts.admin_theme.alert')

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <!-- Filter Button -->
                        <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#filterModal">
                            <i class="fa fa-filter"></i> Filter
                        </button>

                        <!-- Add Transaction & Export Button -->
                        <div>
                            <a href="{{ route('admin.petrol_pump_transactions.create') }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-plus"></i> Add Transaction
                            </a>

                            <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn btn-sm btn-success">
                                <i class="fa fa-file-excel"></i> Export
                            </a>
                        </div>
                    </div>

                    <!-- DataTable (same as before) -->
                    <table id="dataTable" class="table table-bordered table-striped show-search-bar">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Invoice No.</th>
                                <th>Date</th>
                                <th>Petrol Pump</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Balance</th>
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
                                <td>{{ $transaction->petrolPump->name }}</td>
                                <td>{!! $transaction->transaction_type_badge !!}</td>
                                <td>{{ $transaction->formatted_amount }}</td>
                                <td>{{ $transaction->formatted_balance_amount }}</td>
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
                                        method="POST" style="display:inline-block;"
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
</div>
</section>
@endsection

<!-- Modal Filter -->
<div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="filterModalLabel">Filter Transactions</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.petrol_pump_transactions.index') }}">
          
          <!-- Petrol Pump Filter -->
          <div class="form-group">
            <label for="petrol_pump_id">Petrol Pump</label>
            <select name="petrol_pump_id" class="form-control">
              <option value="">All Pumps</option>
              @foreach(\App\Models\PetrolPump::active()->get() as $pump)
                <option value="{{ $pump->id }}"
                  {{ request('petrol_pump_id') == $pump->id ? 'selected' : '' }}>
                  {{ $pump->name }}
                </option>
              @endforeach
            </select>
          </div>

          <!-- Transaction Type Filter -->
          <div class="form-group">
            <label for="transaction_type">Transaction Type</label>
            <select name="transaction_type" id="transaction_type" class="form-control">
              <option value="">Select Type</option>
              <option value="credit" {{ request('transaction_type') == 'credit' ? 'selected' : '' }}>Credit</option>
              <option value="debit" {{ request('transaction_type') == 'debit' ? 'selected' : '' }}>Debit</option>
            </select>
          </div>

          <!-- Invoice Number Filter -->
          <div class="form-group">
            <label for="invoice_number">Invoice Number</label>
            <input type="text" class="form-control" name="invoice_number" id="invoice_number" value="{{ request('invoice_number') }}" placeholder="Invoice Number">
          </div>

          <!-- Date Range Filters -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="from_date">From Date</label>
              <input type="date" class="form-control" name="from_date" id="from_date" value="{{ request('from_date') }}">
            </div>
            <div class="form-group col-md-6">
              <label for="to_date">To Date</label>
              <input type="date" class="form-control" name="to_date" id="to_date" value="{{ request('to_date') }}">
            </div>
          </div>

          <!-- Apply and Reset Buttons -->
          <div class="form-group text-right">
            <button type="submit" class="btn btn-primary">Apply Filter</button>
            <a href="{{ route('admin.petrol_pump_transactions.index') }}" class="btn btn-secondary ml-2">Reset</a>
          </div>
          
        </form>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true
    });
});
</script>
@endpush
