<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Repositories\Interfaces\MasterRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Models\User;

class UserController extends Controller
{
    protected $customerRepository;
    protected $masterRepository;
    protected $userRepository;

    private $currentUserId;

    private $currentUserCustomerId;

    private $currentUserIsCustomer;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        MasterRepositoryInterface $masterRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->masterRepository = $masterRepository;
        $this->userRepository = $userRepository;

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
        Gate::authorize('index_users');
        $users = $this->currentUserIsCustomer == 'Y' ? $this->userRepository->getUsersByCustomerId(Auth::user()->customer_id) : $this->userRepository->getUsers();
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
        $isCustomerUser = $this->currentUserIsCustomer;
        return view('layouts.admin.users.create', compact('customers', 'roles', 'isCustomerUser'));
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

        $addData['customer_id'] = $this->currentUserIsCustomer == 'N' ? $request->customer_id : $this->currentUserCustomerId;
        if (!empty($addData['customer_id'])) {
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
        $isCustomerUser = $this->currentUserIsCustomer;
        return view('layouts.admin.users.create', compact('user', 'customers', 'roles', 'isCustomerUser'));
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

        $data['customer_id'] = $this->currentUserIsCustomer == 'N' ? $request->customer_id : $this->currentUserCustomerId;
        if (!empty($data['customer_id'])) {
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

    public function show()
    {
        $user = Auth::user();
        return view('layouts.admin.profile.index', compact('user'));
    }


    public function editProfile()
    {
        $user = Auth::user();
        return view('layouts.admin.profile.create', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'img' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['name', 'email']);

        if ($request->hasFile('img')) {

            // delete old image
            if ($user->img && file_exists(public_path('uploads/users/' . $user->img))) {
                unlink(public_path('uploads/users/' . $user->img));
            }

            $file = $request->file('img');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/users'), $filename);

            $data['img'] = $filename;
        }

        $user->update($data);

        return redirect()->route('admin.profile.show')->with('success', 'Profile updated successfully');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        // check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Current password is incorrect');
        }

        // update password
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Password updated successfully');
    }
}
