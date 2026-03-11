<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PetrolPump;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PetrolPumpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('index_petrol_pumps_petrol_pumps');
        $petrolPumps = PetrolPump::latest()->get();
        return view('layouts.admin.petrol_pumps.index', compact('petrolPumps'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create_petrol_pumps_petrol_pumps');
        return view('layouts.admin.petrol_pumps.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create_petrol_pumps_petrol_pumps');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'phone' => 'required|string|unique:petrol_pumps',
            'alternate_phone' => 'nullable|string',
            'address' => 'nullable|string',
            'pan_number' => 'nullable|string|max:20',
            'opening_balance' => 'nullable|numeric|min:0',
            'balance_type' => 'required|in:payable,receivable',
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'remarks' => 'nullable|string'
        ]);

        // Set current balance same as opening balance initially
        $validated['current_balance'] = $validated['opening_balance'] ?? 0;

        // If balance type is payable, make current balance negative
        if ($validated['balance_type'] == 'payable' && $validated['current_balance'] > 0) {
            $validated['current_balance'] = -$validated['current_balance'];
        }

        PetrolPump::create($validated);

        return redirect()->route('admin.petrol_pumps.index')
            ->with('success', 'Petrol pump created successfully.');
    }

    /**
     * Display the specified petrol pump.
     */
    public function show(PetrolPump $petrolPump)
    {
        Gate::authorize('read_petrol_pumps_petrol_pumps');
        $transactions = $petrolPump->transactions()->latest()->get();
        return view('layouts.admin.petrol_pumps.show', compact('petrolPump', 'transactions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PetrolPump $petrolPump)
    {
        Gate::authorize('update_petrol_pumps_petrol_pumps');
        return view('layouts.admin.petrol_pumps.create', compact('petrolPump'));
    }

    /**
     * Update the specified petrol pump.
     */
    public function update(Request $request, PetrolPump $petrolPump)
    {
        Gate::authorize('update_petrol_pumps_petrol_pumps');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'phone' => 'required|string|unique:petrol_pumps,phone,' . $petrolPump->id,
            'alternate_phone' => 'nullable|string',
            'address' => 'nullable|string',
            'pan_number' => 'nullable|string|max:20',
            'opening_balance' => 'nullable|numeric|min:0',
            'balance_type' => 'required|in:payable,receivable',
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'remarks' => 'nullable|string'
        ]);

        // Don't update current balance from opening balance if there are transactions
        if ($petrolPump->transactions()->count() == 0) {
            $validated['current_balance'] = $validated['opening_balance'] ?? 0;

            if ($validated['balance_type'] == 'payable' && $validated['current_balance'] > 0) {
                $validated['current_balance'] = -$validated['current_balance'];
            }
        }

        $petrolPump->update($validated);

        return redirect()->route('admin.petrol_pumps.index')
            ->with('success', 'Petrol pump updated successfully.');
    }

    /**
     * Remove the specified petrol pump.
     */
    public function destroy(PetrolPump $petrolPump)
    {
        Gate::authorize('delete_petrol_pumps_petrol_pumps');
        // Check if there are any transactions
        if ($petrolPump->transactions()->count() > 0) {
            return redirect()->route('admin.petrol_pumps.index')
                ->with('error', 'Cannot delete petrol pump with existing transactions.');
        }

        $petrolPump->delete();

        return redirect()->route('admin.petrol_pumps.index')
            ->with('success', 'Petrol pump deleted successfully.');
    }
}
