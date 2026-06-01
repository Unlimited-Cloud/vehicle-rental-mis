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
        // Get all payments with relationships
        $paymentsQuery = Payment::with(['vehicleBooking.customer', 'creator'])
            ->orderBy('payment_date', 'desc');

        if ($request->filled('payment_method')) {

            if (in_array($request->payment_method, ['esewa', 'khalti'])) {

                $paymentsQuery->where('payment_method', 'online')
                    ->where('gateway', $request->payment_method);
            } else {

                $paymentsQuery->where('payment_method', $request->payment_method);
            }
        }

        // if ($request->has('gateway') && $request->gateway != '') {
        //     $paymentsQuery->where('gateway', $request->gateway);
        // }

        if ($request->has('direction') && $request->direction != '') {
            $paymentsQuery->where('direction', $request->direction);
        }

        if ($request->has('status') && $request->status != '') {
            $paymentsQuery->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $paymentsQuery->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $paymentsQuery->whereDate('payment_date', '<=', $request->date_to);
        }

        // Get paginated results
        $payments = $paymentsQuery->get();

        // Dashboard Statistics
        $totalIncome = Payment::income()->completed()->sum('amount');
        $totalExpense = Payment::expense()->completed()->sum('amount');
        $netRevenue = $totalIncome - $totalExpense;

        $totalTransactions = Payment::count();
        $completedCount = Payment::completed()->count();
        $pendingCount = Payment::pending()->count();
        $failedCount = Payment::failed()->count();

        // Payment method breakdown
        $paymentMethods = [];

        $paymentMethods['cash'] = [
            'total' => Payment::where('payment_method', 'cash')
                ->where('direction', 'in')
                ->completed()
                ->sum('amount'),
            'count' => Payment::where('payment_method', 'cash')
                ->where('direction', 'in')
                ->completed()
                ->count(),
            'expense' => Payment::where('payment_method', 'cash')
                ->where('direction', 'out')
                ->completed()
                ->sum('amount'),
        ];

        $paymentMethods['bank_transfer'] = [
            'total' => Payment::where('payment_method', 'bank_transfer')
                ->completed()
                ->sum('amount'),
            'count' => Payment::where('payment_method', 'bank_transfer')
                ->completed()
                ->count(),
            'expense' => Payment::where('payment_method', 'bank_transfer')
                ->where('direction', 'out')
                ->completed()
                ->sum('amount'),
        ];

        $paymentMethods['khalti'] = [
            'total' => Payment::where('gateway', 'khalti')
                ->where('direction', 'in')
                ->completed()
                ->sum('amount'),

            'count' => Payment::where('gateway', 'khalti')
                ->where('direction', 'in')
                ->completed()
                ->count(),

            'expense' => Payment::where('gateway', 'khalti')
                ->where('direction', 'out')
                ->completed()
                ->sum('amount'),
        ];

        $paymentMethods['esewa'] = [
            'total' => Payment::where('gateway', 'esewa')
                ->where('direction', 'in')
                ->completed()
                ->sum('amount'),

            'count' => Payment::where('gateway', 'esewa')
                ->where('direction', 'in')
                ->completed()
                ->count(),

            'expense' => Payment::where('gateway', 'esewa')
                ->where('direction', 'out')
                ->completed()
                ->sum('amount'),
        ];



        // Direction breakdown
        $incomeCount = Payment::income()->count();
        $expenseCount = Payment::expense()->count();

        // Monthly data for chart (last 12 months)
        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyData[] = [
                'month' => $month->format('M Y'),
                'income' => Payment::income()
                    ->completed()
                    ->whereYear('payment_date', $month->year)
                    ->whereMonth('payment_date', $month->month)
                    ->sum('amount'),
                'expense' => Payment::expense()
                    ->completed()
                    ->whereYear('payment_date', $month->year)
                    ->whereMonth('payment_date', $month->month)
                    ->sum('amount')
            ];
        }

        // Recent transactions
        $recentTransactions = Payment::with(['vehicleBooking.customer'])
            ->orderBy('payment_date', 'desc')
            ->take(10)
            ->get();

        return view('layouts.admin.payments.index', compact(
            'payments',
            'totalIncome',
            'totalExpense',
            'netRevenue',
            'totalTransactions',
            'completedCount',
            'pendingCount',
            'failedCount',
            'paymentMethods',
            'incomeCount',
            'expenseCount',
            'monthlyData',
            'recentTransactions'
        ));
    }

    public function show($method, $id)
    {
        $payment = Payment::with(['vehicleBooking.customer', 'vehicleBooking.vehicle', 'creator', 'crew'])
            ->findOrFail($id);

        return view('layouts.admin.payments.show', compact('payment'));
    }

    public function destroy($method, $id)
    {
        try {
            $payment = Payment::findOrFail($id);
            $payment->delete();

            return redirect()->route('admin.payments.index')
                ->with('success', 'Payment record deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.payments.index')
                ->with('error', 'Failed to delete payment record.');
        }
    }

    public function create()
    {
        $bookings = VehicleBooking::with('customer')
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('layouts.admin.payments.create', compact('bookings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_booking_id' => 'nullable|exists:vehicle_bookings,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,esewa,khalti,bank',
            'direction' => 'required|in:in,out',
            'transaction_reference' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,completed,failed,cancelled',
            'proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        $validated['unique_id'] = 'PAY-' . strtoupper(uniqid());
        $validated['created_by'] = auth()->id();
        $validated['created_user_type'] = 'admin';

        if ($request->hasFile('proof')) {
            $validated['proof'] = $request->file('proof')->store('payment-proofs', 'public');
        }

        Payment::create($validated);

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment created successfully.');
    }

    public function edit($id)
    {
        $payment = Payment::findOrFail($id);
        $bookings = VehicleBooking::with('customer')
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('layouts.admin.payments.edit', compact('payment', 'bookings'));
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $validated = $request->validate([
            'vehicle_booking_id' => 'nullable|exists:vehicle_bookings,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,esewa,khalti,bank',
            'direction' => 'required|in:in,out',
            'transaction_reference' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,completed,failed,cancelled',
            'proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        if ($request->hasFile('proof')) {
            $validated['proof'] = $request->file('proof')->store('payment-proofs', 'public');
        }

        $payment->update($validated);

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment updated successfully.');
    }

    public function export(Request $request)
    {
        $payments = Payment::with(['vehicleBooking.customer'])
            ->when($request->date_from, fn($q) => $q->whereDate('payment_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('payment_date', '<=', $request->date_to))
            ->when($request->payment_method, fn($q) => $q->where('payment_method', $request->payment_method))
            ->when($request->direction, fn($q) => $q->where('direction', $request->direction))
            ->get();

        // Return CSV export
        $fileName = 'payments_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($payments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Date', 'Amount', 'Direction', 'Method', 'Status', 'Reference', 'Customer', 'Notes']);

            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->id,
                    $payment->payment_date->format('Y-m-d H:i:s'),
                    $payment->amount,
                    $payment->direction,
                    $payment->payment_method,
                    $payment->status,
                    $payment->transaction_reference,
                    $payment->vehicleBooking?->customer?->name ?? 'N/A',
                    $payment->notes
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function payManualAttendance(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendance,id',
            'proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes' => 'nullable',
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

            $proofPath = null;
            if ($request->hasFile('proof')) {

                $file = $request->file('proof');

                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

                $destinationPath = public_path('uploads/paymentproof');

                // create folder if not exists
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $file->move($destinationPath, $filename);

                // path saved in DB
                $proofPath = 'uploads/paymentproof/' . $filename;
            }


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
                'proof' => $proofPath,
                'notes' => $request->notes,
                'status' => 'completed',
                'created_by' => Auth::id(),
            ]);

            // update attendance
            $attendance->update([
                'payment_status' => 'paid',
                'payment_remarks' => 'Manual Payment(Cash)',
            ]);
        });

        return response()->json([
            'success' => true,
            'payment_url' => route('admin.attendance.index'),
            'message' => 'Payment marked as completed.'
        ]);
    }
}
