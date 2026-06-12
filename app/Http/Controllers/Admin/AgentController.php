<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\User;
use App\Models\Bank;


use App\Models\VehicleBooking;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AgentController extends Controller
{
    /**
     * Display a listing of agents.
     */
    public function index()
    {
        Gate::authorize('index_agents');

        $agents = Agent::with('user')->latest()->get();

        return view('layouts.admin.c-agents.index', compact('agents'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        Gate::authorize('create_agents');

        $users = User::all();
        $banks = Bank::where('is_payee_account', 1)
            ->orderBy('bank_name')
            ->get();

        return view('layouts.admin.c-agents.create', compact('users', 'banks'));
    }

    /**
     * Store new agent.
     */
    public function store(Request $request)
    {
        Gate::authorize('create_agents');

        $request->validate([

            // user info
            'agent_name' => 'required|string|max:255',
            'agent_email' => 'nullable|email|unique:users,email',
            'img' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            // agent info
            'role' => 'required|string',
            'citizenship_doc' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            // bank
            'bank_name' => 'nullable|string',
            'bank_account_name' => 'nullable|string',
            'bank_account_number' => 'nullable|string',
            // wallet
            'wallet_name' => 'nullable|string',
            'wallet_number' => 'nullable|string',
            // other
            'commission_rate' => 'nullable|numeric',
            'remarks' => 'nullable|string',
        ]);

        $data = $request->all();

        $bank = Bank::where('bank_name', $request->bank_name)->first();

        if (!$bank) {
            return back()->withErrors([
                'bank_name' => 'Selected bank not found.'
            ])->withInput();
        }

        $data['bank_code'] = $bank->swift_code;
        if ($request->hasFile('citizenship_doc')) {

            $file = $request->file('citizenship_doc');

            $fileName = time() . '_' . $file->getClientOriginalName();

            $file->move(public_path('uploads/agents/docs'), $fileName);

            $data['citizenship_doc'] = 'uploads/agents/docs/' . $fileName;
        }

        $userData = [];

        $agentName = $request->agent_name;
        $agentEmail = $request->agent_email;
        $agentContact = $request->contact_number;

        $userData['name'] = $agentName;

        if (empty($agentEmail)) {

            $formattedName = strtolower(str_replace(' ', '', $agentName));

            $agentEmail = $formattedName . time() . '@example.com';
        }

        $userData['email'] = $agentEmail;
        $userData['mobile_number_country_code'] = '+977';
        $userData['mobile_number'] = $agentContact;
        $userData['user_type'] = 'agents';
        $userData['role_id'] = '9'; // agent role

        // default password
        $userData['password'] = Hash::make('Nepal@123456');

        // user image
        if ($request->hasFile('img')) {

            $profileImage = $request->file('img');

            $imageName = time() . '_agent_' . $profileImage->getClientOriginalName();

            $profileImage->move(public_path('uploads/users'), $imageName);

            $userData['img'] = $imageName;
        }

        $userData['created_at'] = now();

        $userId = DB::table('users')->insertGetId($userData);

        $data['user_id'] = $userId;

        $data['is_verified'] = $request->is_verified ?? 0;

        $data['status'] = $request->status ?? 1;
        $data['agent_code'] = 'AGT' . strtoupper(Str::random(6));

        Agent::create($data);




        return redirect()->route('admin.agents.index')
            ->with('success', 'Agent created successfully');
    }

    /**
     * Edit agent.
     */
    public function edit(Agent $agent)
    {
        Gate::authorize('update_agents');

        $users = User::all();

        $agent = Agent::select(
            'users.name as agent_name',
            'users.email as agent_email',
            'users.img as user_img',
            'agents.*'
        )
            ->join('users', 'users.id', '=', 'agents.user_id')
            ->where('agents.id', $agent->id)
            ->first();

        $banks = Bank::where('is_payee_account', 1)
            ->orderBy('bank_name')
            ->get();

        return view('layouts.admin.c-agents.create', compact('agent', 'users', 'banks'));
    }

    /**
     * Update agent.
     */
    public function update(Request $request, Agent $agent)
    {
        Gate::authorize('update_agents');

        $request->validate([

            'agent_name' => 'required|string|max:255',

            'agent_email' => 'nullable|email|unique:users,email,' . $agent->user_id,

            'img' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'role' => 'required|string',

            'citizenship_doc' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',

            'contact_number' => 'nullable|string|max:20',

            'address' => 'nullable|string',

            'bank_name' => 'nullable|string',

            'bank_account_name' => 'nullable|string',

            'bank_account_number' => 'nullable|string',

            'wallet_name' => 'nullable|string',

            'wallet_number' => 'nullable|string',

            'commission_rate' => 'nullable|numeric',

            'remarks' => 'nullable|string',
        ]);

        $data = $request->all();


        if ($request->hasFile('citizenship_doc')) {

            if (
                $agent->citizenship_doc &&
                file_exists(public_path($agent->citizenship_doc))
            ) {
                unlink(public_path($agent->citizenship_doc));
            }

            $file = $request->file('citizenship_doc');

            $fileName = time() . '_' . $file->getClientOriginalName();

            $file->move(public_path('uploads/agents/docs'), $fileName);

            $data['citizenship_doc'] = 'uploads/agents/docs/' . $fileName;
        }


        $userData = [];

        $userData['name'] = $request->agent_name;
        $userData['mobile_number'] = $request->contact_number;
        $userData['mobile_number_country_code'] = '+977';

        $agentEmail = $request->agent_email;

        if (empty($agentEmail)) {

            $formattedName = strtolower(
                str_replace(' ', '', $request->agent_name)
            );

            $agentEmail = $formattedName . time() . '@example.com';
        }

        $userData['email'] = $agentEmail;

        // update image
        if ($request->hasFile('img')) {

            $user = DB::table('users')
                ->where('id', $agent->user_id)
                ->first();

            if ($user && $user->img) {

                if (file_exists(public_path('uploads/users/' . $user->img))) {

                    unlink(public_path('uploads/users/' . $user->img));
                }
            }

            $profileImage = $request->file('img');

            $imageName = time() . '_agent_' . $profileImage->getClientOriginalName();

            $profileImage->move(public_path('uploads/users'), $imageName);

            $userData['img'] = $imageName;
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

        DB::table('users')
            ->where('id', $agent->user_id)
            ->update($userData);

        $agent->update($data);

        return redirect()->route('admin.agents.index')
            ->with('success', 'Agent updated successfully');
    }

    /**
     * Show agent details.
     */
    public function show(Agent $agent)
    {
        Gate::authorize('read_agents');

        $agent->load('user');

        return view('layouts.admin.c-agents.show', compact('agent'));
    }

    /**
     * Delete agent.
     */
    public function destroy(Agent $agent)
    {
        Gate::authorize('delete_agents');

        // delete citizenship doc
        if (
            $agent->citizenship_doc &&
            file_exists(public_path($agent->citizenship_doc))
        ) {
            unlink(public_path($agent->citizenship_doc));
        }

        // delete user image
        $user = DB::table('users')
            ->where('id', $agent->user_id)
            ->first();

        if ($user && $user->img) {

            if (file_exists(public_path('uploads/users/' . $user->img))) {

                unlink(public_path('uploads/users/' . $user->img));
            }
        }

        // delete user
        DB::table('users')
            ->where('id', $agent->user_id)
            ->delete();

        // delete agent
        $agent->delete();

        return redirect()->route('admin.agents.index')
            ->with('success', 'Agent deleted successfully');
    }





    //Agent Booking
    public function agentBookingindex(Request $request)
    {
        Gate::authorize('index_agents');

        $agents = Agent::with('user')->get();

        $query = VehicleBooking::whereNotNull('agent_code')
            ->where('agent_code', '!=', '');

        if ($request->filled('agent_code')) {
            $query->where('agent_code', $request->agent_code);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $bookings = $query->latest()->get();

        // Agent map keyed by agent_code for O(1) lookup
        $agentMap = $agents->keyBy('agent_code');

        // Plain array of booking IDs that already have commission paid
        $paidBookingIds = DB::table('payments')
            ->where('payment_type', 'agent_commission')
            ->where('status', 'completed')
            ->where('payment_method', 'bank_transfer')
            ->pluck('vehicle_booking_id')
            ->toArray();

        $failBookingIds = DB::table('payments')
            ->where('payment_type', 'agent_commission')
            ->where('status', 'failed')
            ->where('payment_method', 'bank_transfer')
            ->pluck('vehicle_booking_id')
            ->toArray();




        // Single loop — attach agent, calculate commission, resolve paid flag
        foreach ($bookings as $booking) {
            $agent                   = $agentMap->get($booking->agent_code);
            $booking->agent          = $agent;
            $booking->commissionRate = $agent ? (float) $agent->commission_rate : 0;
            if (
                $booking->sub_total === null ||
                $booking->sub_total === '' ||
                (float) $booking->sub_total == 0
            ) {
                $baseAmount = $booking->rate_per_day;
            } else {
                $baseAmount = $booking->sub_total;
            }
            $discountAmount = 0;

            if ($booking->discount > 0) {
                if ($booking->discount_amount_type === 'percentage') {
                    $discountAmount = ($baseAmount * $booking->discount) / 100;
                } else {
                    $discountAmount = $booking->discount;
                }
            }

            // dd($baseAmount, $discountAmount);

            // Net before VAT
            $commissionBase = max(0, $baseAmount - $discountAmount);
            $booking->commissionBase = $commissionBase;
            $booking->commissionAmt = ($commissionBase * $booking->commissionRate) / 100;
            $booking->isPaid         = in_array($booking->id, $paidBookingIds);
        }

        // Summary
        $totalBookings     = $bookings->count();
        $totalCommission   = $bookings->sum('commissionAmt');
        $paidCommission    = $bookings->where('isPaid', true)->sum('commissionAmt');
        $pendingCommission = $totalCommission - $paidCommission;

        return view('layouts.admin.c-agents.agent-bookings', compact(
            'bookings',
            'agents',
            'totalBookings',
            'totalCommission',
            'paidCommission',
            'pendingCommission',
            'paidBookingIds',
            'failBookingIds'
        ));
    }

    // Agent details for modal (will be used in payment step)
    public function agentDetails(Request $request)
    {
        $request->validate(['agent_code' => 'required|string']);

        $agent = Agent::with('user')
            ->where('agent_code', $request->agent_code)
            ->first();

        if (!$agent) {
            return response()->json(['success' => false, 'message' => 'Agent not found']);
        }

        return response()->json([
            'success' => true,
            'agent'   => [
                'name'                => $agent->user->name ?? 'N/A',
                'wallet_name'         => $agent->wallet_name,
                'wallet_number'       => $agent->wallet_number,
                'bank_name'           => $agent->bank_name,
                'bank_account_name'   => $agent->bank_account_name,
                'bank_account_number' => $agent->bank_account_number,
                'commission_rate'     => $agent->commission_rate,
            ],
        ]);
    }
}
