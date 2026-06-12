<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Repositories\Interfaces\VehicleOwnerRepositoryInterface;

class VehicleOwnerController extends Controller
{
    protected $vehicleownerRepository;
    private $currentUserId;

    private $currentUserCustomerId;

    private $currentUserIsCustomer;

    public function __construct(
        VehicleOwnerRepositoryInterface $vehicleownerRepository
    ) {
        $this->vehicleownerRepository = $vehicleownerRepository;

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
        Gate::authorize('index_vehicles_vehicle_owner');
        $currentUserIsCustomer = $this->currentUserIsCustomer;
        $vehicleowners = $this->currentUserIsCustomer == 'Y' ? $this->vehicleownerRepository->getVehicleOwnerById($this->currentUserCustomerId) : $this->vehicleownerRepository->getAllVehicleOwner();
        return view('layouts.admin.vehicleowner.index', compact('vehicleowners', 'currentUserIsCustomer'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create_vehicles_vehicle_owner');
        return view('layouts.admin.vehicleowner.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create_vehicles_vehicle_owner');
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

        VehicleOwner::create($validated);

        return redirect()->route('admin.vehicleowner.index')
            ->with('success', 'Vehicle Owner created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleOwner $vehicleowner)
    {
        Gate::authorize('read_vehicles_vehicle_owner');
        $vehicleowner->load('vehicles');

        return view('layouts.admin.vehicleowner.show', compact('vehicleowner'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VehicleOwner $vehicleowner)
    {
        Gate::authorize('update_vehicles_vehicle_owner');
        return view('layouts.admin.vehicleowner.create', compact('vehicleowner'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, VehicleOwner $vehicleowner)
    {
        Gate::authorize('update_vehicles_vehicle_owner');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:vehicle_owners,email,' . $vehicleowner->id,
            'phone' => 'required|string|unique:vehicle_owners,phone,' . $vehicleowner->id,
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

        $vehicleowner->update($validated);

        return redirect()->route('admin.vehicleowner.index')
            ->with('success', 'Vehicle Owner updated successfully.');
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(VehicleOwner $vehicleowner)
    {
        Gate::authorize('delete_vehicles_vehicle_owner');
        $vehicleowner->delete();

        return redirect()->route('admin.vehicleowner.index')
            ->with('success', 'Vehicle Owner deleted successfully.');
    }
}
