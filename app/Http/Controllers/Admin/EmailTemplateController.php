<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\EmailtemplateActivities;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class EmailTemplateController extends Controller
{

    public function index()
    {
        Gate::authorize('index_emails_email_templates');
        $emailTemplates = EmailTemplate::with('emailActivities')
            ->orderBy('id', 'desc')
            ->get();

        return view('layouts.admin.email-templates.index', compact('emailTemplates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create_emails_email_templates');
        $activities = EmailtemplateActivities::all();

        return view('layouts.admin.email-templates.create', compact('activities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create_emails_email_templates');
        $request->validate([
            'title' => 'required|string|max:255',
            'activity' => 'required|string',
            'template_for' => 'nullable|string|max:255',
            'partner_Uuid' => 'nullable|exists:partners,Uuid',
            'activity_UUID' => 'nullable|exists:emailtemplate_activities,Uuid',
            'delay_min' => 'nullable|integer',
            'delay_hour' => 'nullable|integer',
            'delay_days' => 'nullable|integer',
            'email_subject' => 'required|string',
            'success_email_content' => 'nullable|string',
            'success_sms_content' => 'nullable|string',
            'error_email_content' => 'nullable|string',
            'error_sms_content' => 'nullable|string',
            'success_customer_notification_content' => 'nullable|string',
            'success_admin_notification_content' => 'nullable|string',
            'email_cc' => 'nullable|string',
            'email_template_triggered' => 'nullable|boolean',
            'sms_template_triggered' => 'nullable|boolean',
            'notification_template_triggered' => 'nullable|boolean',
        ]);

        $data = $request->all();
        $data['template_UUID'] = 'Et-' . (string) Str::uuid();

        EmailTemplate::create($data);

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Email template created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        Gate::authorize('read_emails_email_templates');
        $emailTemplate = EmailTemplate::with('emailActivities')
            ->findOrFail($id);

        return view('layouts.admin.email-templates.show', compact('emailTemplate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        Gate::authorize('update_emails_email_templates');
        $emailTemplate = EmailTemplate::findOrFail($id);
        $activities = EmailtemplateActivities::all();

        return view('layouts.admin.email-templates.edit', compact('emailTemplate', 'activities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Gate::authorize('update_emails_email_templates');
        $request->validate([
            'title' => 'required|string|max:255',
            'activity' => 'required|string',
            'template_for' => 'nullable|string|max:255',
            'partner_Uuid' => 'nullable|exists:partners,Uuid',
            'activity_UUID' => 'nullable|exists:emailtemplate_activities,Uuid',
            'delay_min' => 'nullable|integer',
            'delay_hour' => 'nullable|integer',
            'delay_days' => 'nullable|integer',
            'email_subject' => 'required|string',
            'success_email_content' => 'nullable|string',
            'success_sms_content' => 'nullable|string',
            'error_email_content' => 'nullable|string',
            'error_sms_content' => 'nullable|string',
            'success_customer_notification_content' => 'nullable|string',
            'success_admin_notification_content' => 'nullable|string',
            'email_cc' => 'nullable|string',
            'email_template_triggered' => 'nullable|boolean',
            'sms_template_triggered' => 'nullable|boolean',
            'notification_template_triggered' => 'nullable|boolean',
        ]);

        $emailTemplate = EmailTemplate::findOrFail($id);
        $emailTemplate->update($request->all());

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Email template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Gate::authorize('delete_emails_email_templates');
        $emailTemplate = EmailTemplate::findOrFail($id);
        $emailTemplate->delete();

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Email template deleted successfully.');
    }
}
