{{-- resources/views/admin/payments/index.blade.php --}}
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
    .stat-icon {
        font-size: 2.5rem;
        opacity: 0.3;
        position: absolute;
        right: 15px;
        top: 15px;
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
                <h3>{{ number_format($totalRevenue, 2) }}</h3>
                <p>Total Revenue (NPR)</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success stat-card">
            <div class="inner">
                <h3>{{ $totalTransactions }}</h3>
                <p>Total Transactions</p>
            </div>
            <div class="icon">
                <i class="fas fa-credit-card"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning stat-card">
            <div class="inner">
                <h3>{{ $pendingCount }}</h3>
                <p>Pending Payments</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger stat-card">
            <div class="inner">
                <h3>{{ $failedCount }}</h3>
                <p>Failed Payments</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
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
                    <div class="col-md-6 text-center">
                        <div class="info-box bg-gradient-info">
                            <div class="info-box-content">
                                <span class="info-box-text">eSewa Revenue</span>
                                <span class="info-box-number">रु {{ number_format($esewaTotal, 2) }}</span>
                                <span class="info-box-text">Transactions: {{ $esewaCount }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-center">
                        <div class="info-box bg-gradient-purple">
                            <div class="info-box-content">
                                <span class="info-box-text">Khalti Revenue</span>
                                <span class="info-box-number">रु {{ number_format($khaltiTotal, 2) }}</span>
                                <span class="info-box-text">Transactions: {{ $khaltiCount }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="progress-group mt-3">
                    <span class="progress-text">eSewa Share</span>
                    <span class="float-right"><b>{{ $totalRevenue > 0 ? round(($esewaTotal / $totalRevenue) * 100, 1) : 0 }}%</b></span>
                    <div class="progress sm">
                        <div class="progress-bar bg-info" style="width: {{ $totalRevenue > 0 ? ($esewaTotal / $totalRevenue) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <div class="progress-group">
                    <span class="progress-text">Khalti Share</span>
                    <span class="float-right"><b>{{ $totalRevenue > 0 ? round(($khaltiTotal / $totalRevenue) * 100, 1) : 0 }}%</b></span>
                    <div class="progress sm">
                        <div class="progress-bar bg-purple" style="width: {{ $totalRevenue > 0 ? ($khaltiTotal / $totalRevenue) * 100 : 0 }}%"></div>
                    </div>
                </div>
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
                                <span class="info-box-text">Success Rate</span>
                                <span class="info-box-number">{{ $totalTransactions > 0 ? round(($completedCount / $totalTransactions) * 100, 1) : 0 }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-card">
    <form method="GET" action="{{ route('admin.payments.index') }}">
        <div class="row">
            <div class="col-md-3">
                <label>Payment Method</label>
                <select name="payment_method" class="form-control">
                    <option value="">All Methods</option>
                    <option value="esewa" {{ request('payment_method') == 'esewa' ? 'selected' : '' }}>eSewa</option>
                    <option value="khalti" {{ request('payment_method') == 'khalti' ? 'selected' : '' }}>Khalti</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                    <option value="Complete" {{ request('status') == 'Complete' ? 'selected' : '' }}>Completed (Esewa)</option>
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
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="dataTable" class="table table-bordered table-striped show-search-bar">
                <thead>
                    <tr>
                        <th>S.N.</th>
                        <th>Transaction ID</th>
                        <th>Payment Method</th>
                        <th>Customer</th>
                        <th>Amount (NPR)</th>
                        <th>Status</th>
                        <th>Payment Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $key => $payment)
                    <tr>
                        <td>{{ $payments->firstItem() + $key }}</td>
                        <td>
                            <small class="text-muted">{{ substr($payment->transaction_id, 0, 15) }}...</small>
                        </td>
                        <td>
                            @if($payment->payment_method == 'esewa')
                                <span class="badge badge-primary">eSewa</span>
                            @else
                                <span class="badge badge-purple">Khalti</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $payment->customer_name }}</strong><br>
                            <small>{{ $payment->customer_phone }}</small>
                        </td>
                        <td>
                            <strong>रु {{ number_format($payment->total_amount ?? $payment->amount, 2) }}</strong>
                            @if($payment->payment_method == 'khalti' && isset($payment->fees))
                                <br><small class="text-muted">Fee: रु {{ number_format($payment->fees, 2) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($payment->status == 'Completed' || $payment->status == 'completed')
                                <span class="badge badge-success">Completed</span>
                            @elseif($payment->status == 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($payment->status == 'failed')
                                <span class="badge badge-danger">Failed</span>
                            @else
                                <span class="badge badge-secondary">{{ $payment->status }}</span>
                            @endif
                        </td>
                        <td>
                            {{ $payment->payment_date->format('Y-m-d H:i:s') }}
                        </td>
                        <td>
                            <a href="{{ route('admin.payments.show', ['method' => $payment->payment_method, 'id' => $payment->id]) }}" 
                               class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i> View
                            </a>
                            
                            <form action="{{ route('admin.payments.destroy', ['method' => $payment->payment_method, 'id' => $payment->id]) }}"
                                  method="POST"
                                  style="display:inline-block;"
                                  onsubmit="return confirm('Are you sure you want to delete this payment record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">
                            <i class="fas fa-inbox fa-3x text-muted"></i>
                            <p class="mt-2">No payment records found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $payments->links() }}
        </div>
    </div>
</div>

</div>
</section>
@endsection
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