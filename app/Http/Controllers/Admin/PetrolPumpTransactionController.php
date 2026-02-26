<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PetrolPumpTransaction;
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\PetrolPumpTransactionExport;

use App\Models\PetrolPump;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PetrolPumpTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PetrolPumpTransaction::with(['petrolPump', 'vehicle', 'customer']);

        if ($request->filled('petrol_pump_id')) {
            $query->where('petrol_pump_id', $request->petrol_pump_id);
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }

        if ($request->has('export')) {
            return Excel::download(
                new PetrolPumpTransactionExport($request),
                'petrol_pump_transactions.xlsx'
            );
        }

        $transactions = $query->latest()->get();
        $petrolPumps = PetrolPump::active()->get();

        return view('layouts.admin.petrol_pump_transactions.index', compact('transactions', 'petrolPumps'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $petrolPumps = PetrolPump::active()->get();
        $vehicles    = Vehicle::where('status', '1')->get();
        $petrol_pump_id = $request->input('petrol_pump_id');
        $vehicle_id     = $request->input('vehicle_id');
        $customer_id    = $request->input('customer_id');

        return view(
            'layouts.admin.petrol_pump_transactions.create',
            compact(
                'petrolPumps',
                'vehicles',
                'petrol_pump_id',
                'vehicle_id',
                'customer_id'
            )
        );
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'petrol_pump_id' => 'required|exists:petrol_pumps,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:credit,debit,payment,payable',
            'amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'fuel_quantity' => 'nullable|numeric|min:0',
            'fuel_type' => 'nullable|in:petrol,diesel,cng,other',
            'rate_per_liter' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'reference_number' => 'nullable|string',
            'remarks' => 'nullable|string',
            'odometer_reading' => 'nullable|string',
            'status' => 'required|in:pending,completed,cancelled'
        ]);

        DB::transaction(function () use ($validated) {

            $paidAmount = $validated['paid_amount'] ?? 0;
            $validated['paid_amount'] = $paidAmount;

            // First create transaction without balance
            $transaction = PetrolPumpTransaction::create($validated);

            if ($validated['status'] === 'completed') {

                $newBalance = $this->updatePumpBalance(
                    $validated['petrol_pump_id'],
                    $validated['amount'],
                    $validated['transaction_type'],
                    'apply'
                );

                // 🔥 Update transaction balance as running balance
                $transaction->update([
                    'balance' => $newBalance
                ]);
            }
        });
        return redirect()->route('admin.petrol_pump_transactions.index')
            ->with('success', 'Transaction created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PetrolPumpTransaction $petrolPumpTransaction)
    {
        $petrolPumpTransaction->load('petrolPump');
        return view(
            'layouts.admin.petrol_pump_transactions.show',
            compact('petrolPumpTransaction')
        );
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit(PetrolPumpTransaction $petrolPumpTransaction)
    {
        $petrolPumps = PetrolPump::active()->get();
        $vehicles    = Vehicle::where('status', '1')->get();
        return view(
            'layouts.admin.petrol_pump_transactions.create',
            compact('petrolPumps', 'vehicles', 'petrolPumpTransaction')
        );
    }

    /**
     * Update the specified transaction.
     */
    public function update(Request $request, PetrolPumpTransaction $petrolPumpTransaction)
    {
        $validated = $request->validate([
            'petrol_pump_id' => 'required|exists:petrol_pumps,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:credit,debit,payment,payable',
            'amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'fuel_quantity' => 'nullable|numeric|min:0',
            'fuel_type' => 'nullable|in:petrol,diesel,cng,other',
            'rate_per_liter' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'reference_number' => 'nullable|string',
            'remarks' => 'nullable|string',
            'status' => 'required|in:pending,completed,cancelled'
        ]);

        DB::transaction(function () use ($validated, $petrolPumpTransaction) {

            // Revert old balance if completed
            if ($petrolPumpTransaction->status === 'completed') {
                $this->updatePumpBalance(
                    $petrolPumpTransaction->petrol_pump_id,
                    $petrolPumpTransaction->amount,
                    $petrolPumpTransaction->transaction_type,
                    'revert'
                );
            }

            $petrolPumpTransaction->update($validated);

            if ($validated['status'] === 'completed') {

                $newBalance = $this->updatePumpBalance(
                    $validated['petrol_pump_id'],
                    $validated['amount'],
                    $validated['transaction_type'],
                    'apply'
                );

                $petrolPumpTransaction->update([
                    'balance' => $newBalance
                ]);
            }
        });

        return redirect()->route('admin.petrol_pump_transactions.index')
            ->with('success', 'Transaction updated successfully.');
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy(PetrolPumpTransaction $petrolPumpTransaction)
    {
        DB::transaction(function () use ($petrolPumpTransaction) {

            if ($petrolPumpTransaction->status === 'completed') {
                $this->updatePumpBalance(
                    $petrolPumpTransaction->petrol_pump_id,
                    $petrolPumpTransaction->amount,
                    $petrolPumpTransaction->transaction_type,
                    'revert'
                );
            }

            $petrolPumpTransaction->delete();
        });

        return redirect()->route('admin.petrol_pump_transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }

    public function getPumpBalance($id)
    {
        $pump = PetrolPump::find($id);

        if (!$pump) {
            return response()->json(['error' => 'Pump not found'], 404);
        }

        return response()->json([
            'current_balance' => $pump->current_balance,
            'formatted_balance' => $pump->formatted_current_balance,
            'balance_type' => $pump->balance_type,
            'credit_limit' => $pump->credit_limit,
            'is_limit_exceeded' => $pump->is_credit_limit_exceeded
        ]);
    }

    private function updatePumpBalance($petrolPumpId, $amount, $transactionType, $action = 'apply')
    {
        $pump = PetrolPump::lockForUpdate()->find($petrolPumpId);

        if (!$pump) {
            return 0;
        }

        $multiplier = ($action === 'revert') ? -1 : 1;

        if (in_array($transactionType, ['credit', 'payable'])) {
            $pump->current_balance += ($amount * $multiplier);
        }

        if (in_array($transactionType, ['debit', 'payment'])) {
            $pump->current_balance -= ($amount * $multiplier);
        }

        $pump->save();

        return $pump->current_balance; // 🔥 return updated balance
    }
}
