<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PetrolPumpTransaction;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Gate;

use App\Exports\PetrolPumpTransactionExport;
use App\Models\CrewProfile;
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
        Gate::authorize('index_petrol_pumps_transactions');
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
        Gate::authorize('create_petrol_pumps_transactions');
        $petrolPumps = PetrolPump::active()->get();
        $vehicles    = Vehicle::where('status', '1')->get();
        $petrol_pump_id = $request->input('petrol_pump_id');
        $vehicle_id     = $request->input('vehicle_id');
        $customer_id    = $request->input('customer_id');
        $drivers = CrewProfile::where('role', 'driver')
            ->with('user')
            ->get();

        return view(
            'layouts.admin.petrol_pump_transactions.create',
            compact(
                'petrolPumps',
                'vehicles',
                'petrol_pump_id',
                'vehicle_id',
                'customer_id',
                'drivers'
            )
        );
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create_petrol_pumps_transactions');

        // Validate request
        $validated = $request->validate([
            'petrol_pump_id' => 'required|exists:petrol_pumps,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:credit,debit,payment,payable',
            'paid_amount' => 'nullable|numeric|min:0',
            'fuel_quantity' => 'nullable|numeric|min:0',
            'fuel_type' => 'nullable|in:petrol,diesel,cng,other',
            'rate_per_liter' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'reference_number' => 'nullable|string',
            'remarks' => 'nullable|string',
            'odometer_reading' => 'nullable|string',
            'status' => 'required|in:pending,completed,cancelled',
            'pump_before' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'pump_after' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'tank_before' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'tank_after' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'driver_id' => 'nullable|exists:users,id',
        ]);

        // Calculate amount
        $amount = ($validated['fuel_quantity'] ?? 0) * ($validated['rate_per_liter'] ?? 0);
        $validated['amount'] = $amount;

        DB::transaction(function () use ($request, &$validated) {

            $validated['paid_amount'] = $validated['paid_amount'] ?? 0;

            $uploadPath = public_path('uploads/fuel_purchased');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // Handle image uploads
            foreach (['pump_before', 'pump_after', 'tank_before', 'tank_after'] as $imageField) {
                if ($request->hasFile($imageField)) {
                    $file = $request->file($imageField);
                    $filename = uniqid() . '_' . $imageField . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadPath, $filename);
                    $validated[$imageField] = 'uploads/fuel_purchased/' . $filename;
                }
            }

            // Create transaction
            $transaction = PetrolPumpTransaction::create($validated);

            // Update pump balance if transaction is completed
            if ($validated['status'] === 'completed') {
                $newBalance = $this->updatePumpBalance(
                    $validated['petrol_pump_id'],
                    $validated['amount'],
                    $validated['transaction_type'],
                    'apply'
                );

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
        Gate::authorize('view_petrol_pumps_transactions');
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
        Gate::authorize('update_petrol_pumps_transactions');
        $petrolPumps = PetrolPump::active()->get();
        $vehicles    = Vehicle::where('status', '1')->get();
        $drivers = CrewProfile::where('role', 'driver')
            ->with('user')
            ->get();
        return view(
            'layouts.admin.petrol_pump_transactions.create',
            compact('petrolPumps', 'vehicles', 'petrolPumpTransaction', 'drivers')
        );
    }

    /**
     * Update the specified transaction.
     */
    public function update(Request $request, PetrolPumpTransaction $petrolPumpTransaction)
    {
        Gate::authorize('update_petrol_pumps_transactions');

        // Validate request
        $validated = $request->validate([
            'petrol_pump_id' => 'required|exists:petrol_pumps,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:credit,debit,payment,payable',
            'amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'fuel_quantity' => 'nullable|numeric|min:0',
            'fuel_type' => 'nullable|in:petrol,diesel,cng,other',
            'rate_per_liter' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'reference_number' => 'nullable|string',
            'remarks' => 'nullable|string',
            'status' => 'required|in:pending,completed,cancelled',
            'pump_before' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'pump_after' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'tank_before' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'tank_after' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'driver_id' => 'nullable|exists:users,id',
        ]);

        DB::transaction(function () use ($request, &$validated, $petrolPumpTransaction) {

            // Revert old balance if previously completed
            if ($petrolPumpTransaction->status === 'completed') {
                $this->updatePumpBalance(
                    $petrolPumpTransaction->petrol_pump_id,
                    $petrolPumpTransaction->amount,
                    $petrolPumpTransaction->transaction_type,
                    'revert'
                );
            }

            $uploadPath = public_path('uploads/fuel_purchased');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // Handle image uploads
            foreach (['pump_before', 'pump_after', 'tank_before', 'tank_after'] as $imageField) {
                if ($request->hasFile($imageField)) {
                    $file = $request->file($imageField);
                    $filename = uniqid() . '_' . $imageField . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadPath, $filename);

                    // Update the field in validated data
                    $validated[$imageField] = 'uploads/fuel_purchased/' . $filename;

                    // Optionally delete old image
                    $oldImage = $petrolPumpTransaction->{$imageField};
                    if ($oldImage && file_exists(public_path($oldImage))) {
                        @unlink(public_path($oldImage));
                    }
                }
            }

            // Update transaction
            $petrolPumpTransaction->update($validated);

            // Recalculate and update balance if completed
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
        Gate::authorize('delete_petrol_pumps_transactions');
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
