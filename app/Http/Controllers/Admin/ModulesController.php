<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Module;
use App\Services\ConfigurationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use App\Repositories\Interfaces\MasterRepositoryInterface;

class ModulesController extends Controller
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
        Gate::authorize('index_configuration_modules');
        $modules = $this->masterRepository->getAllModules();
        return view('layouts.admin.modules.index', compact('modules'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        Gate::authorize('create_configuration_modules');
        $modules = $this->masterRepository->getParentModules();
        return view('layouts.admin.modules.create', compact('modules'));
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        Gate::authorize('create_configuration_modules');
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
        ]);
        $this->configurationService->storeModules($request);
        return redirect()->route('admin.modules.index')
            ->with('success', 'Modules Created Successfully');
    }

    /**
     * Show edit form
     */
    public function edit(Module $module)
    {
        Gate::authorize('update_configuration_modules');
        $modules = $this->masterRepository->getAllModules();
        return view('layouts.admin.modules.create', compact('module','modules'));
    }

    /**
     * Update user
     */
    public function update(Request $request, Module $module)
    {
        Gate::authorize('update_configuration_modules');
        $request->validate([
           'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
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

        return redirect()->route('admin.modules.index')
            ->with('success', 'Module Updated Successfully');
    }

    /**
     * Delete user
     */
    public function destroy(Module $module)
    {
        Gate::authorize('delete_configuration_modules');
        $module->delete();

        return redirect()->route('admin.modules.index')
            ->with('success', 'Module Deleted Successfully');
    }
}
