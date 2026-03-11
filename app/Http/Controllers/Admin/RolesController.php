<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Validator;


class RolesController extends Controller
{

    protected $userRepository;
    public function __construct(UserRepositoryInterface $userRepository)
    {

        $this->userRepository = $userRepository;
    }

    public function index()
    {
        return view('layouts.admin.roles.index', [
            'roles' => $this->userRepository->getAllRoles()
        ]);
    }


    public function add()
    {

       $allPer  =  $this->userRepository->getAllPermissions();
 
        return view('layouts.admin.roles.form', [
            'permissions' => $this->userRepository->getAllPermissions()
        ]);
    }

    public function edit($id)
    {

        $roleswithPermissions = $this->userRepository->getRolesWithPermissions($id);
        
        return view('layouts.admin.roles.form', [
            'roleswithPermissions' => $roleswithPermissions,
            'permissions' => $this->userRepository->getAllPermissions(),
            'role' => DB::table('roles')->where('id', $id)->first(), // Assuming there's only one role
        ]);
    }


    public function store(Request $request)
    {


        $code = 200;
        $message = "Roles created Successfully";
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'permissions' => 'nullable'
            ], [
                'name' => "Role is required",
                'status' => 'Status is required'
            ]);


            // Use a transaction for data integrity
            DB::transaction(function () use ($validatedData) {
                // Create the role
                $role = Role::create([
                    'name' => $validatedData['name'],
                    'status' => $validatedData['status'],
                ]);

                // Prepare permissions for bulk insert
                $permissionsRoles = array_map(function ($permission) use ($role) {
                    return [
                        'permission_id' => $permission,
                        'role_id' => $role->id,
                    ];
                }, $validatedData['permissions']);
                DB::table('permission_role')->insert($permissionsRoles);
            });
        } catch (\Throwable $th) {


            $code = 500;
            $message = "Roles created Successfully";
        }




        return response()->json([
            'message' => $message,
            'redirect_url' => route('admin.user_roles.index')
        ], $code);
    }



    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'nullable'
        ], [
            'name' => "Role is required",
            'status' => 'Status is required'
        ]);

        $code  = 200;
        $message = "Role updated successfully";
        try {
            // Use a transaction for data integrity
            DB::transaction(function () use ($validatedData, $id) {
                // Create the role
                $role = Role::where('id', $id)->update([
                    'name' => $validatedData['name']
                ]);

                // Sync permissions: this will remove all old permissions and add new ones
                $role = Role::findOrFail($id); // Retrieve the role instance
                $role->permissions()->sync($validatedData['permissions'] ?? []); // Sync permissions

            });
        }catch (\Throwable $th) {
            dd($th->getMessage());
            $code  = 500;
            $message = "Something went wrong";
        }

        return redirect()->route('admin.user_roles.index')
            ->with('success', 'Roles Updated successfully.');
    }


    public function delete($id)
    {
        dd($id);
    }
}
