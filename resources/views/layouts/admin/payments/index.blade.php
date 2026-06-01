{{-- resources/views/layouts/admin/payments/index.blade.php --}}
@extends('layouts.admin_theme.container')

@section('dynamicdata')
<style>
    .stat-card {
        transition: transform 0.3s;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .badge-purple {
        background-color: #6f42c1;
        color: white;
    }
    .filter-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .direction-income {
        border-left: 4px solid #28a745;
    }
    .direction-expense {
        border-left: 4px solid #dc3545;
    }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Payments Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Payments</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
<div class="container-fluid">

@include('layouts.admin_theme.alert')

<!-- Statistics Cards -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info stat-card">
            <div class="inner">
                <h3>{{ number_format($totalIncome, 2) }}</h3>
                <p>Total Income (NPR)</p>
            </div>
            <div class="icon">
                <i class="fas fa-arrow-down"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger stat-card">
            <div class="inner">
                <h3>{{ number_format($totalExpense, 2) }}</h3>
                <p>Total Expense (NPR)</p>
            </div>
            <div class="icon">
                <i class="fas fa-arrow-up"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success stat-card">
            <div class="inner">
                <h3>{{ number_format($netRevenue, 2) }}</h3>
                <p>Net Revenue (NPR)</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning stat-card">
            <div class="inner">
                <h3>{{ $totalTransactions }}</h3>
                <p>Total Transactions</p>
            </div>
            <div class="icon">
                <i class="fas fa-credit-card"></i>
            </div>
        </div>
    </div>
</div>

<!-- Payment Method Breakdown -->
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie"></i> Payment Method Breakdown
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach(['cash' => 'success', 'esewa' => 'info', 'khalti' => 'purple', 'bank_transfer' => 'primary'] as $method => $color)
                    <div class="col-md-6 text-center mb-3">
                        <div class="info-box bg-gradient-{{ $color }}">
                            <div class="info-box-content">
                                <span class="info-box-text">{{ ucfirst($method) }} Revenue</span>
                                <span class="info-box-number">रु {{ number_format($paymentMethods[$method]['total'], 2) }}</span>
                                <span class="info-box-text">Transactions: {{ $paymentMethods[$method]['count'] }}</span>
                                @if($paymentMethods[$method]['expense'] > 0)
                                <small>Expense: रु {{ number_format($paymentMethods[$method]['expense'], 2) }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                @foreach(['cash', 'esewa', 'khalti', 'bank_transfer'] as $method)
                @if($totalIncome > 0)
                <div class="progress-group">
                    <span class="progress-text">{{ ucfirst($method) }}</span>
                    <span class="float-right"><b>{{ round(($paymentMethods[$method]['total'] / $totalIncome) * 100, 1) }}%</b></span>
                    <div class="progress sm">
                        <div class="progress-bar bg-{{ $method == 'cash' ? 'success' : ($method == 'esewa' ? 'info' : ($method == 'khalti' ? 'purple' : 'primary')) }}" 
                             style="width: {{ ($paymentMethods[$method]['total'] / $totalIncome) * 100 }}%"></div>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i> Quick Stats
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Completed</span>
                                <span class="info-box-number">{{ $completedCount }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-hourglass-half"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pending</span>
                                <span class="info-box-number">{{ $pendingCount }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Failed</span>
                                <span class="info-box-number">{{ $failedCount }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-chart-line"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Income/Expense</span>
                                <span class="info-box-number">{{ $incomeCount }} / {{ $expenseCount }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Chart -->
{{-- <div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar"></i> Monthly Income vs Expense
                </h3>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div> --}}

<!-- Filter Section -->
<div class="filter-card">
    <form method="GET" action="{{ route('admin.payments.index') }}">
        <div class="row">
            <div class="col-md-2">
                <label>Payment Method</label>
                <select name="payment_method" class="form-control">
                    <option value="">All Methods</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="esewa" {{ request('payment_method') == 'esewa' ? 'selected' : '' }}>eSewa</option>
                    <option value="khalti" {{ request('payment_method') == 'khalti' ? 'selected' : '' }}>Khalti</option>
                    <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Direction</label>
                <select name="direction" class="form-control">
                    <option value="">All</option>
                    <option value="in" {{ request('direction') == 'in' ? 'selected' : '' }}>Income</option>
                    <option value="out" {{ request('direction') == 'out' ? 'selected' : '' }}>Expense</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label>Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <label>&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
                        <i class="fas fa-sync-alt"></i> Reset
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Payments Table -->
<div class="card card-primary card-outline card-tabs">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i> Payment Transactions
        </h3>
        {{-- <div class="card-tools">
            <a href="{{ route('layouts.admin.payments.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Payment
            </a>
            <a href="{{ route('layouts.admin.payments.export', request()->query()) }}" class="btn btn-success btn-sm">
                <i class="fas fa-download"></i> Export
            </a>
        </div> --}}
    </div>
    <div class="card-body">
        <div class="table-responsive">
             <table id="dataTable" class="table table-bordered table-striped show-search-bar">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Transaction ID</th>
                        <th>Direction</th>
                        <th>Method</th>
                        <th>Customer/Reference</th>
                        <th>Amount (NPR)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr class="{{ $payment->direction == 'in' ? 'direction-income' : 'direction-expense' }}">
                        <td>#{{ $payment->id }}</td>
                        <td>{{ $payment->payment_date->format('Y-m-d H:i') }}</td>
                        <td>
                            <small class="text-muted">{{ $payment->unique_id }}</small>
                            @if($payment->transaction_reference)
                            <br><small>{{ $payment->transaction_reference }}</small>
                            @endif
                        </td>
                        <td>{!! $payment->direction_badge !!}</td>
                        <td>{!! $payment->payment_method_badge !!}</td>
                        <td>
                            @if($payment->vehicleBooking && $payment->vehicleBooking->customer)
                                <strong>{{ $payment->vehicleBooking->customer->name }}</strong><br>
                                <small>Booking: #{{ $payment->vehicle_booking_id }}</small>
                            @else
                                <span class="text-muted">{{ $payment->notes ?: 'N/A' }}</span>
                            @endif
                        </td>
                        <td>
                            <strong>रु {{ number_format($payment->amount, 2) }}</strong>
                        </td>
                        <td>{!! $payment->status_badge !!}</td>
                        <td>
                        <a href="{{ route('admin.payments.show', [
                                'method' => $payment->payment_method,
                                'id' => $payment->id
                            ]) }}"
                            class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                           
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
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">
                            <i class="fas fa-inbox fa-3x text-muted"></i>
                            <p class="mt-2">No payment records found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
       
    </div>
</div>

</div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // ← Add id="paymentsTable" to your <table> in the blade, then:
       $('#dataTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true
    });

    // Monthly Chart
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyData = @json($monthlyData);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthlyData.map(d => d.month),
            datasets: [
                {
                    label: 'Income',
                    data: monthlyData.map(d => d.income),
                    backgroundColor: 'rgba(40, 167, 69, 0.5)',
                    borderColor: '#28a745',
                    borderWidth: 1
                },
                {
                    label: 'Expense',
                    data: monthlyData.map(d => d.expense),
                    backgroundColor: 'rgba(220, 53, 69, 0.5)',
                    borderColor: '#dc3545',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'रु ' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': रु ' + context.raw.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush