<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Repositories\Interfaces\MasterRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $customerRepository;
    protected $masterRepository;

    protected $isCustomerUser;
    protected $userCustomerId;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        MasterRepositoryInterface $masterRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->masterRepository = $masterRepository;
        $this->isCustomerUser = !empty(Auth::user()->customer_id) ? 'Y' : 'N';
        $this->userCustomerId = !empty(Auth::user()) ? Auth::user()->customer_id : NULL;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('index_users');
        
        $users = User::latest()->get();
        return view('layouts.admin.users.index', compact('users'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        Gate::authorize('create_users');
        $customers = $this->customerRepository->getAllCustomers();
        $roles = $this->masterRepository->getAllRoles();
        $isCustomerUser = $this->isCustomerUser;
        return view('layouts.admin.users.create', compact('customers','roles','isCustomerUser'));
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        Gate::authorize('create_users');
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $addData = [
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role_id'  => $request->role_id,
        ];

        $addData['customer_id'] = $this->isCustomerUser == 'N' ? $request->customer_id : $this->userCustomerId;
        if(!empty($addData['customer_id'])){
            $addData['user_type'] = 'customer_dashboard';
        }

        User::create($addData);

        return redirect()->route('admin.users.index')
            ->with('success', 'User Created Successfully');
    }

    /**
     * Show edit form
     */
    public function edit(User $user)
    {
        Gate::authorize('update_users');
        $customers = $this->customerRepository->getAllCustomers();
        $roles = $this->masterRepository->getAllRoles();
        return view('layouts.admin.users.create', compact('user','customers','roles'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        Gate::authorize('update_users');
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $data = [
            'name'    => $request->name,
            'email'   => $request->email,
            'role_id' => $request->role_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $data['customer_id'] = $request->customer_id;
        if(!empty($data['customer_id'])){
            $data['user_type'] = 'customer_dashboard';
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User Updated Successfully');
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        Gate::authorize('delete_users');
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User Deleted Successfully');
    }
}
