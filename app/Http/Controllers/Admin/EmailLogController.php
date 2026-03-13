<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EmailLogController extends Controller
{
    public function index()
    {
        Gate::authorize('index_emails_email_logs');
        $emailLogs = EmailLog::with('emailTemplate')
            ->orderBy('id', 'desc')
            ->get();

        return view('layouts.admin.email-logs.index', compact('emailLogs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create_emails_email_logs');
        $emailTemplates = EmailTemplate::all();
        return view('layouts.admin.email-logs.create', compact('emailTemplates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create_emails_email_logs');
        $request->validate([
            'emailtemplate_id' => 'nullable|exists:email_templates,id',
            'email_from' => 'required|email|max:255',
            'email_to' => 'required|email|max:255',
            'email_subject' => 'required|string|max:255',
            'email_body' => 'required|string',
            'email_cc' => 'nullable|string|max:255',
            'status' => 'required|in:pending,sent,failed',
            'failure_reason' => 'nullable|string',
        ]);

        EmailLog::create($request->all());

        return redirect()->route('admin.email-logs.index')
            ->with('success', 'Email log created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        Gate::authorize('read_emails_email_logs');
        $emailLog = EmailLog::with('emailTemplate')
            ->findOrFail($id);

        return view('layouts.admin.email-logs.show', compact('emailLog'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        Gate::authorize('update_emails_email_logs');
        $emailLog = EmailLog::findOrFail($id);
        $emailTemplates = EmailTemplate::all();

        return view('layouts.admin.email-logs.edit', compact('emailLog', 'emailTemplates'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Gate::authorize('update_emails_email_logs');
        $request->validate([
            'emailtemplate_id' => 'nullable|exists:email_templates,id',
            'email_from' => 'required|email|max:255',
            'email_to' => 'required|email|max:255',
            'email_subject' => 'required|string|max:255',
            'email_body' => 'required|string',
            'email_cc' => 'nullable|string|max:255',
            'status' => 'required|in:pending,sent,failed',
            'failure_reason' => 'nullable|string',
        ]);

        $emailLog = EmailLog::findOrFail($id);
        $emailLog->update($request->all());

        return redirect()->route('admin.email-logs.index')
            ->with('success', 'Email log updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Gate::authorize('delete_emails_email_logs');
        $emailLog = EmailLog::findOrFail($id);
        $emailLog->delete();

        return redirect()->route('admin.email-logs.index')
            ->with('success', 'Email log deleted successfully.');
    }
}
