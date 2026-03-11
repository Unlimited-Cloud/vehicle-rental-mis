<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('index_customers');
        $customers = Customer::latest()->get();
        return view('layouts.admin.customers.index', compact('customers'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('layouts.admin.customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:customers',
            'phone' => 'required|string|unique:customers',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pan_number' => 'nullable|string|max:20',
            'license_number' => 'nullable|string|max:50',
            'license_expiry' => 'nullable|date',
            'status' => 'required|in:active,inactive'
        ]);

        if (!empty($validated['name'])) {
            $nameParts = explode(' ', trim($validated['name']));
            $count = count($nameParts);

            if ($count == 1) {
                // Only one name: treat as first name
                $validated['first_name'] = $nameParts[0];
                $validated['middle_name'] = null;
                $validated['last_name'] = null;
            } elseif ($count == 2) {
                // Two names: first and last
                $validated['first_name'] = $nameParts[0];
                $validated['middle_name'] = null;
                $validated['last_name'] = $nameParts[1];
            } elseif ($count >= 3) {
                // Three or more names: first, middle (all middle parts), last
                $validated['first_name'] = $nameParts[0];
                $validated['last_name'] = $nameParts[$count - 1];

                // Everything in between is middle name
                if ($count > 2) {
                    $middleParts = array_slice($nameParts, 1, -1);
                    $validated['middle_name'] = implode(' ', $middleParts);
                }
            }
        }

        Customer::create($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return view('layouts.admin.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('layouts.admin.customers.create', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone' => 'required|string|unique:customers,phone,' . $customer->id,
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pan_number' => 'nullable|string|max:20',
            'license_number' => 'nullable|string|max:50',
            'license_expiry' => 'nullable|date',
            'status' => 'required|in:active,inactive'
        ]);

        if (!empty($validated['name'])) {
            $nameParts = explode(' ', trim($validated['name']));
            $count = count($nameParts);

            if ($count == 1) {
                // Only one name: treat as first name
                $validated['first_name'] = $nameParts[0];
                $validated['middle_name'] = null;
                $validated['last_name'] = null;
            } elseif ($count == 2) {
                // Two names: first and last
                $validated['first_name'] = $nameParts[0];
                $validated['middle_name'] = null;
                $validated['last_name'] = $nameParts[1];
            } elseif ($count >= 3) {
                // Three or more names: first, middle (all middle parts), last
                $validated['first_name'] = $nameParts[0];
                $validated['last_name'] = $nameParts[$count - 1];

                // Everything in between is middle name
                if ($count > 2) {
                    $middleParts = array_slice($nameParts, 1, -1);
                    $validated['middle_name'] = implode(' ', $middleParts);
                }
            }
        }

        $customer->update($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
