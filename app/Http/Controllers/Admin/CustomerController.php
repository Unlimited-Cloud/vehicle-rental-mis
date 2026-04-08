<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Repositories\Interfaces\CustomerRepositoryInterface;

class CustomerController extends Controller
{
    protected $customerRepository;
    private $currentUserId;

    private $currentUserCustomerId;

    private $currentUserIsCustomer;

    public function __construct(
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;

        $this->middleware(function ($request, $next) {
            $this->currentUserId = Auth::user()->id;
            $this->currentUserCustomerId = Auth::user()->customer_id;
            $this->currentUserIsCustomer = !empty(Auth::user()->customer_id) ? 'Y' : 'N';
            return $next($request);
        });
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('index_customers');
        $currentUserIsCustomer = $this->currentUserIsCustomer;
        $customers = $this->currentUserIsCustomer == 'Y' ? $this->customerRepository->getCustomerById($this->currentUserCustomerId) : $this->customerRepository->getAllCustomers();
        return view('layouts.admin.customers.index', compact('customers', 'currentUserIsCustomer'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create_customers');
        return view('layouts.admin.customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create_customers');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:customers',
            'phone' => 'nullable|string|unique:customers',
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
        Gate::authorize('read_customers');
        return view('layouts.admin.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        Gate::authorize('update_customers');
        return view('layouts.admin.customers.create', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        Gate::authorize('update_customers');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|unique:customers,phone,' . $customer->id,
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
        Gate::authorize('delete_customers');
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    public function storeAjax(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'phone' => 'nullable|max:20',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'id' => $customer->id,
            'name' => $customer->name
        ]);
    }
    public function listAjax()
    {
        return Customer::select('id', 'name')->get();
    }
}
