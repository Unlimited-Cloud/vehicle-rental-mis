<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\EsewaPayment;
use App\Models\KhaltiPayment;
use App\Models\Payment;
use App\Models\VehicleBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        // Get all eSewa payments
        $esewaPayments = EsewaPayment::with('booking')->get()->map(function ($payment) {
            return (object)[
                'id' => $payment->id,
                'transaction_id' => $payment->transaction_uuid,
                'amount' => $payment->amount,
                'total_amount' => $payment->amount,
                'status' => $payment->status,
                'payment_method' => 'esewa',
                'customer_name' => $payment->booking->customer->name ?? 'N/A',
                'customer_email' => $payment->booking->customer->email ?? 'N/A',
                'customer_phone' => $payment->booking->customer->phone ?? 'N/A',
                'booking_id' => $payment->booking_id,
                'payment_date' => $payment->created_at,
                'raw_response' => $payment->esewa_response
            ];
        });

        // Get all Khalti payments
        $khaltiPayments = KhaltiPayment::with('booking')->get()->map(function ($payment) {
            return (object)[
                'id' => $payment->id,
                'transaction_id' => $payment->pidx ?? $payment->txn_id ?? $payment->merchant_transaction_id,
                'amount' => $payment->amount,
                'fees' => $payment->fees,
                'total_amount' => $payment->total_amount,
                'status' => $payment->status,
                'payment_method' => 'khalti',
                'customer_name' => $payment->user_name ?? $payment->booking->customer->name ?? 'N/A',
                'customer_email' => $payment->user_email ?? $payment->booking->customer->email ?? 'N/A',
                'customer_phone' => $payment->user_mobile ?? $payment->booking->customer->phone ?? 'N/A',
                'booking_id' => $payment->booking_id,
                'payment_date' => $payment->created_at,
                'raw_response' => $payment->khalti_init_response
            ];
        });

        // Merge and sort payments
        $payments = $esewaPayments->concat($khaltiPayments)
            ->sortByDesc('payment_date');

        // Apply filters
        if ($request->has('payment_method') && $request->payment_method != '') {
            $payments = $payments->where('payment_method', $request->payment_method);
        }

        if ($request->has('status') && $request->status != '') {
            $payments = $payments->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $payments = $payments->where('payment_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $payments = $payments->where('payment_date', '<=', $request->date_to . ' 23:59:59');
        }

        // Pagination
        $currentPage = request()->get('page', 1);
        $perPage = 15;
        $currentItems = $payments->slice(($currentPage - 1) * $perPage, $perPage);
        $payments = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $payments->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Dashboard Statistics
        $totalRevenue = $esewaPayments->where('status', 'Completed')->sum('amount') +
            $khaltiPayments->where('status', 'Completed')->sum('total_amount');

        $totalTransactions = $payments->count();
        $completedCount = $payments->where('status', 'Completed')->count() +
            $payments->where('status', 'Completed')->count();
        $pendingCount = $payments->where('status', 'pending')->count();
        $failedCount = $payments->where('status', 'failed')->count();

        $esewaTotal = $esewaPayments->where('status', 'Completed')->sum('amount');
        $khaltiTotal = $khaltiPayments->where('status', 'Completed')->sum('total_amount');
        $esewaCount = $esewaPayments->where('status', 'Completed')->count();
        $khaltiCount = $khaltiPayments->where('status', 'Completed')->count();

        // Recent transactions (last 10)
        $recentTransactions = $payments->take(10);

        return view('layouts.admin.payments.index', compact(
            'payments',
            'totalRevenue',
            'totalTransactions',
            'completedCount',
            'pendingCount',
            'failedCount',
            'esewaTotal',
            'khaltiTotal',
            'esewaCount',
            'khaltiCount',
            'recentTransactions'
        ));
    }

    public function show($method, $id)
    {
        if ($method == 'esewa') {
            $payment = EsewaPayment::with('booking')->findOrFail($id);
            return view('layouts.admin.payments.show', compact('payment', 'method'));
        } else {
            $payment = KhaltiPayment::with('booking')->findOrFail($id);
            return view('layouts.admin.payments.show', compact('payment', 'method'));
        }
    }

    public function destroy($method, $id)
    {
        if ($method == 'esewa') {
            $payment = EsewaPayment::findOrFail($id);
            $payment->delete();
        } else {
            $payment = KhaltiPayment::findOrFail($id);
            $payment->delete();
        }

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment record deleted successfully');
    }



    public function payManualAttendance(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendance,id'
        ]);

        $attendance = Attendance::with('crew.user')->findOrFail($request->attendance_id);

        // prevent duplicate payment
        $existing = Payment::where('attendance_id', $attendance->id)
            ->where('status', 'completed')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Already paid for this attendance.'
            ], 422);
        }

        DB::transaction(function () use ($attendance, $request) {

            // create payment record
            Payment::create([
                'attendance_id' => $attendance->id,
                'vehicle_booking_id' => $attendance->booking_id,
                'crew_id' => $attendance->crew_id,
                'amount' => $attendance->allowances,
                'payment_method' => 'cash',
                'transaction_reference' => 'MANUAL-' . strtoupper(Str::random(8)),
                'payment_date' => now(),
                'payment_type' => "attendance",
                'status' => 'completed',
                'created_by' => Auth::id(),
            ]);

            // update attendance
            $attendance->update([
                'payment_status' => 'paid',
                'remarks' => 'Manual Payment',
            ]);
        });

        return response()->json([
            'success' => true,
            'payment_url' => route('admin.attendance.index'),
            'message' => 'Payment marked as completed.'
        ]);
    }
}
