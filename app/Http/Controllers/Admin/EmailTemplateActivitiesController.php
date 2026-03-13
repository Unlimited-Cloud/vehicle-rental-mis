<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmailtemplateActivities;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class EmailTemplateActivitiesController extends Controller
{
    public function index()
    {
        Gate::authorize('index_emails_email_template_activities');
        $activities = EmailtemplateActivities::with('emailTemplate')
            ->orderBy('id', 'desc')
            ->get();

        return view('layouts.admin.emailtemplate_activities.index', compact('activities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create_emails_email_template_activities');
        // $partners = Partners::where('status', 1)->get();
        return view('layouts.admin.emailtemplate_activities.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create_emails_email_template_activities');
        $request->validate([
            'activity_for' => 'required|string|max:255',
            'partner_Uuid' => 'nullable|exists:partners,Uuid',
            'activity' => 'required|string',
            'email_triggered' => 'nullable|boolean',
            'sms_triggered' => 'nullable|boolean',
            'notification_triggered' => 'nullable|boolean',
        ]);

        $data = $request->all();
        $data['Uuid'] = (string) Str::uuid();
        $data['added_by'] = auth()->id();

        EmailtemplateActivities::create($data);

        return redirect()->route('admin.emailtemplate_activities.index')
            ->with('success', 'Email template activity created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        Gate::authorize('read_emails_email_template_activities');
        $activity = EmailtemplateActivities::with('emailTemplate')
            ->findOrFail($id);

        return view('layouts.admin.emailtemplate_activities.show', compact('activity'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        Gate::authorize('update_emails_email_template_activities');
        $activity = EmailtemplateActivities::findOrFail($id);
        // $partners = Partners::where('status', 1)->get();

        return view('layouts.admin.emailtemplate_activities.edit', compact('activity'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Gate::authorize('update_emails_email_template_activities');
        $request->validate([
            'activity_for' => 'required|string|max:255',
            'partner_Uuid' => 'nullable|exists:partners,Uuid',
            'activity' => 'required|string',
            'email_triggered' => 'nullable|boolean',
            'sms_triggered' => 'nullable|boolean',
            'notification_triggered' => 'nullable|boolean',
        ]);

        $activity = EmailtemplateActivities::findOrFail($id);

        $data = $request->all();
        $data['updated_by'] = auth()->id();

        $activity->update($data);

        return redirect()->route('admin.emailtemplate_activities.index')
            ->with('success', 'Email template activity updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Gate::authorize('delete_emails_email_template_activities');
        $activity = EmailtemplateActivities::findOrFail($id);
        $activity->deleted_by = auth()->id();
        $activity->save();
        $activity->delete();

        return redirect()->route('admin.emailtemplate_activities.index')
            ->with('success', 'Email template activity deleted successfully.');
    }
}
