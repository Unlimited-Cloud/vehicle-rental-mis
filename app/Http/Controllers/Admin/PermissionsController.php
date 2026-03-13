<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Module;
use App\Models\Permission;
use App\Services\ConfigurationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use App\Repositories\Interfaces\MasterRepositoryInterface;

class PermissionsController extends Controller
{
    protected $masterRepository;
    protected $configurationService;

    public function __construct(
        MasterRepositoryInterface $masterRepository,
        ConfigurationService $configurationService
    ) {
        $this->masterRepository = $masterRepository;
        $this->configurationService = $configurationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('index_configuration_permissions');
        $permissions = $this->masterRepository->getAllPermissions();
        return view('layouts.admin.permissions.index', compact('permissions'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        Gate::authorize('create_configuration_permissions');
        $modules = $this->masterRepository->getAllModules();
        $permissions = $this->masterRepository->getAllPermissions();
        return view('layouts.admin.permissions.create', compact('permissions','modules'));
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        Gate::authorize('create_configuration_permissions');
        $request->validate([
            'module_id' => 'required',
        ]);
        $this->configurationService->storePermissions($request);
        return redirect()->route('admin.permissions.index')
            ->with('success', 'Modules Created Successfully');
    }

    /**
     * Show edit form
     */
    public function edit(Permission $permission)
    {
        Gate::authorize('update_configuration_modules');
        $modules = $this->masterRepository->getAllModules();
        $permissions = $this->masterRepository->getAllPermissions();
        return view('layouts.admin.permissions.create', compact('permission','permissions','modules'));
    }

    /**
     * Update user
     */
    public function update(Request $request, Module $module)
    {
        Gate::authorize('update_configuration_permissions');
        $request->validate([
           'module_id' => 'required',
        ]);

        $data = [
            'name'     => $request->name,
            'parent_id'    => $request->parent_id,
            'icon' => $request->icon,
            'route'  => $request->route,
            'permission'  => $request->permission,
            'order_by'  => $request->order_by,
        ];

        $module->update($data);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Module Updated Successfully');
    }

    /**
     * Delete user
     */
    public function destroy(Module $module)
    {
        Gate::authorize('delete_configuration_permissions');
        $module->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Module Deleted Successfully');
    }
}
