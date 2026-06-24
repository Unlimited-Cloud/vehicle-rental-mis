<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleOwner;
use App\Models\Agent;
use App\Models\Bank;
use App\Models\CommissionStatement;
use App\Models\VehicleBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $banks = Bank::where('is_payee_account', 1)
            ->orderBy('bank_name')
            ->get();
        return view('layouts.admin.vehicleowner.create', compact('banks'));
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
            'status' => 'required|in:active,inactive',
            'bank_name' => 'nullable|string|max:255',
            'bank_code' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'wallet_name' => 'nullable|string|max:255',
            'wallet_number' => 'nullable|string|max:255',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
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
        $bank = Bank::where('bank_name', $request->bank_name)->first();

        if (!$bank) {
            return back()->withErrors([
                'bank_name' => 'Selected bank not found.'
            ])->withInput();
        }

        $data['bank_code'] = $bank->swift_code;

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

        // Get all vehicle IDs owned by this owner
        $vehicleIds = $vehicleowner->vehicles->pluck('id')->toArray();

        // Get payments for bookings that belong to these vehicles
        $payments = collect(); // Empty collection as fallback

        if (!empty($vehicleIds)) {
            $payments = DB::table('payments')
                ->join('vehicle_bookings', 'payments.vehicle_booking_id', '=', 'vehicle_bookings.id')
                ->join('vehicles', 'vehicle_bookings.vehicle_id', '=', 'vehicles.id')
                ->whereIn('vehicles.id', $vehicleIds)
                ->where('payments.payment_type', 'owner_payout')
                ->orderBy('payments.created_at', 'desc')
                ->select(
                    'payments.*',
                    'vehicle_bookings.file_no',
                    'vehicle_bookings.id as booking_id',
                    'vehicles.registration_number'
                )
                ->get();
        }

        return view('layouts.admin.vehicleowner.show', compact('vehicleowner', 'payments'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VehicleOwner $vehicleowner)
    {
        Gate::authorize('update_vehicles_vehicle_owner');
        $banks = Bank::where('is_payee_account', 1)
            ->orderBy('bank_name')
            ->get();
        return view('layouts.admin.vehicleowner.create', compact('vehicleowner', 'banks'));
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
            'status' => 'required|in:active,inactive',
            'bank_name' => 'nullable|string|max:255',
            'bank_code' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'wallet_name' => 'nullable|string|max:255',
            'wallet_number' => 'nullable|string|max:255',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
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

        if ($request->filled('bank_name')) {

            $bank = Bank::where('bank_name', $request->bank_name)->first();

            if (!$bank) {
                return back()->withErrors([
                    'bank_name' => 'Selected bank not found.'
                ])->withInput();
            }

            $data['bank_code'] = $bank->swift_code;
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



    public function ownerBookingIndex(Request $request)
    {
        Gate::authorize('index_vehicles_owner_bookings');

        $query = VehicleBooking::with([
            'vehicle.vehicleOwner'
        ])->whereHas('vehicleMoment', function ($q) {
            $q->whereNotNull('start_datetime')
                ->whereNotNull('end_datetime');
        })->where('payment_status', 1);

        if ($request->filled('owner_id')) {
            $query->whereHas('vehicle', function ($q) use ($request) {
                $q->where('vehicle_owner_id', $request->owner_id);
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $bookings = $query->latest()->get();

        // Plain array of booking IDs that already have commission paid
        $paidBookingIds = DB::table('payments')
            ->where('payment_type', 'owner_payout')
            ->where('status', 'completed')
            ->where('payment_method', 'bank_transfer')
            ->pluck('vehicle_booking_id')
            ->toArray();

        $failBookingIds = DB::table('payments')
            ->where('payment_type', 'owner_payout')
            ->where('status', 'failed')
            ->where('payment_method', 'bank_transfer')
            ->pluck('vehicle_booking_id')
            ->toArray();

        $paidOwnerCommission = DB::table('payments')
            ->where('payment_type', 'owner_payout')
            ->where('status', 'completed')
            ->where('payment_method', 'bank_transfer')
            ->sum('amount');

        // Actual TDS / net paid figures for bookings already paid out,
        // since TDS is chosen manually per-payment and can vary.
        $ownerStatements = CommissionStatement::where('payee_type', 'owner')
            ->whereIn('vehicle_booking_id', $bookings->pluck('id'))
            ->get()
            ->keyBy('vehicle_booking_id');

        $defaultTdsRate = 1.5; // preview rate shown for unpaid bookings only

        foreach ($bookings as $booking) {

            $owner = $booking->vehicle?->vehicleOwner;

            $booking->vehicleOwner = $owner;
            $total_amount = $booking->total_amount ?? 0;

            // Base amount
            $baseAmount = (
                $booking->sub_total === null ||
                $booking->sub_total === '' ||
                (float) $booking->sub_total == 0
            )
                ? $booking->rate_per_day
                : $booking->sub_total;

            // Discount
            $discountAmount = 0;

            if ($booking->discount > 0) {
                if ($booking->discount_amount_type === 'percentage') {
                    $discountAmount = ($baseAmount * $booking->discount) / 100;
                } else {
                    $discountAmount = $booking->discount;
                }
            }

            $amountAfterDiscount = max(0, $baseAmount - $discountAmount);

            /*
     * Remove VAT/Tax
     * Adjust according to your tax structure.
     */
            $taxAmount = $booking->tax_amount ?? 0;

            $amountExcludingTax = max(
                0,
                $amountAfterDiscount - $taxAmount
            );

            /*
     * Agent commission
     */
            $agentCommissionRate = 0;

            if ($booking->agent_code) {
                $agent = Agent::where(
                    'agent_code',
                    $booking->agent_code
                )->first();

                $agentCommissionRate = $agent?->commission_rate ?? 0;
            }

            $agentCommission =
                ($amountExcludingTax * $agentCommissionRate) / 100;

            /*
     * Platform commission
     */
            $platformCommissionRate =
                $owner?->commission_rate ?? 0;

            $platformCommission =
                ($amountExcludingTax * $platformCommissionRate) / 100;

            /*
     * Owner payable (before TDS)
     */
            $ownerPayable =
                $amountExcludingTax
                - $agentCommission
                - $platformCommission;

            $ownerPayable = max(0, $ownerPayable);

            $statement = $ownerStatements->get($booking->id);

            if ($statement) {
                // Already paid — use the real TDS rate/amount that was applied
                $booking->tdsRate       = (float) $statement->tds_rate;
                $booking->tdsAmount     = (float) $statement->tds_amount;
                $booking->netPayable    = (float) $statement->net_paid_amount;
                $booking->tdsIsEstimate = false;
            } else {
                // Not paid yet — show an estimate at the default rate as a preview
                $estimatedTdsAmount = round(($ownerPayable * $defaultTdsRate) / 100, 2);
                $booking->tdsRate       = $defaultTdsRate;
                $booking->tdsAmount     = $estimatedTdsAmount;
                $booking->netPayable    = max(0, $ownerPayable - $estimatedTdsAmount);
                $booking->tdsIsEstimate = true;
            }

            $booking->amountExcludingTax = $amountExcludingTax;
            $booking->agentCommission = $agentCommission;
            $booking->platformCommission = $platformCommission;
            $booking->ownerPayable = $ownerPayable; // pre-TDS, used for the actual payout call
            $booking->isPaid         = in_array($booking->id, $paidBookingIds);
        }

        $totalOwnerPayable = $bookings->sum('ownerPayable');

        return view(
            'layouts.admin.vehicleowner.owner-bookings',
            compact(
                'bookings',
                'totalOwnerPayable',
                'total_amount',
                'paidBookingIds',
                'failBookingIds',
                'paidOwnerCommission'
            )
        );
    }

    public function ownerDetails(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|integer'
        ]);

        $owner = VehicleOwner::find($request->owner_id);

        if (!$owner) {
            return response()->json([
                'success' => false,
                'message' => 'Owner not found'
            ]);
        }

        return response()->json([
            'success' => true,
            'owner' => [
                'bank_name' => $owner->bank_name,
                'bank_account_name' => $owner->bank_account_name,
                'bank_account_number' => $owner->bank_account_number,
                'wallet_name' => $owner->wallet_name,
                'wallet_number' => $owner->wallet_number,
                'commission_rate' => $owner->commission_rate,
            ]
        ]);
    }
}
