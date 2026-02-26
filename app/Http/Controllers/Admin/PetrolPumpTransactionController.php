<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PetrolPumpTransaction;
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

        // Filter by vehicle if requested
        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        // Filter by customer if requested
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }

        $transactions = $query->latest()->get();

        return view('layouts.admin.petrol_pump_transactions.index', compact('transactions'));
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
            // Set paid_amount to 0 if not provided
            if (!isset($validated['paid_amount'])) {
                $validated['paid_amount'] = 0;
            }

            // Calculate balance
            $validated['balance'] = $validated['amount'] - $validated['paid_amount'];

            // Create transaction
            $transaction = PetrolPumpTransaction::create($validated);

            // Update petrol pump balance if status is completed
            if ($validated['status'] == 'completed') {
                $petrolPump = PetrolPump::find($validated['petrol_pump_id']);
                $petrolPump->updateBalance($validated['amount'], $validated['transaction_type']);
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
            // Set paid_amount to 0 if not provided
            if (!isset($validated['paid_amount'])) {
                $validated['paid_amount'] = 0;
            }

            // Calculate balance
            $validated['balance'] = $validated['amount'] - $validated['paid_amount'];

            // Revert old balance if previous status was completed
            if ($petrolPumpTransaction->status == 'completed') {
                $petrolPump = PetrolPump::find($petrolPumpTransaction->petrol_pump_id);
                $petrolPump->revertBalanceUpdate(
                    $petrolPumpTransaction->amount,
                    $petrolPumpTransaction->transaction_type
                );
            }

            // Update transaction
            $petrolPumpTransaction->update($validated);

            // Apply new balance if new status is completed
            if ($validated['status'] == 'completed') {
                $petrolPump = PetrolPump::find($validated['petrol_pump_id']);
                $petrolPump->updateBalance($validated['amount'], $validated['transaction_type']);
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
            // Revert balance if transaction was completed
            if ($petrolPumpTransaction->status == 'completed') {
                $petrolPump = PetrolPump::find($petrolPumpTransaction->petrol_pump_id);
                $petrolPump->revertBalanceUpdate(
                    $petrolPumpTransaction->amount,
                    $petrolPumpTransaction->transaction_type
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
}
