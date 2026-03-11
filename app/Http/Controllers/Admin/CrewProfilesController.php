<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CrewProfile;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class CrewProfilesController extends Controller
{
    public function index()
    {
        Gate::authorize('index_crew_profiles');
        $crew = CrewProfile::with('user')->latest()->get();
        return view('layouts.admin.crew_profiles.index', compact('crew'));
    }

    public function create()
    {
        Gate::authorize('create_crew_profiles');
        $users = User::all();
        return view('layouts.admin.crew_profiles.create', compact('users'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create_crew_profiles');
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:driver,helper',
            'license_number' => 'nullable|string',
            'license_expiry' => 'nullable|date',
            'citizenship_doc' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'contact_number' => 'nullable|string',
        ]);

        $data = $request->all();

        if ($request->hasFile('citizenship_doc')) {
            $file = $request->file('citizenship_doc');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/crew_docs'), $fileName);
            $data['citizenship_doc'] = 'uploads/crew_docs/' . $fileName; // correct path saved in DB
        }

        CrewProfile::create($data);

        return redirect()->route('admin.crew_profiles.index')
            ->with('success', 'Crew profile saved successfully');
    }

    public function edit(CrewProfile $crew_profile)
    {
        Gate::authorize('update_crew_profiles');
        $users = User::all();
        return view('layouts.admin.crew_profiles.create', compact('crew_profile', 'users'));
    }

    public function update(Request $request, CrewProfile $crew_profile)
    {
        Gate::authorize('update_crew_profiles');
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:driver,helper',
            'license_number' => 'nullable|string',
            'license_expiry' => 'nullable|date',
            'citizenship_doc' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'contact_number' => 'nullable|string',
        ]);

        if ($request->hasFile('citizenship_doc')) {
            if ($crew_profile->citizenship_doc && file_exists(public_path($crew_profile->citizenship_doc))) {
                unlink(public_path($crew_profile->citizenship_doc));
            }
            $file = $request->file('citizenship_doc');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/crew_docs'), $fileName);
            $data['citizenship_doc'] = 'uploads/crew_docs/' . $fileName;
        }

        $crew_profile->update($request->all());

        return redirect()->route('admin.crew_profiles.index')
            ->with('success', 'Crew profile updated successfully');
    }

    public function show(CrewProfile $crew_profile)
    {
        Gate::authorize('read_crew_profiles');
        $crew_profile->load('user');
        return view('layouts.admin.crew_profiles.show', compact('crew_profile'));
    }

    public function destroy(CrewProfile $crew_profile)
    {
        Gate::authorize('delete_crew_profiles');
        $crew_profile->delete();

        return redirect()->route('admin.crew_profiles.index')
            ->with('success', 'Crew profile deleted successfully');
    }
}
