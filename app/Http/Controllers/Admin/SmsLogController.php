<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Repositories\Interfaces\MasterRepositoryInterface;

class SmsLogController extends Controller
{
    protected $masterRepository;

    public function __construct(
        MasterRepositoryInterface $masterRepository
    ) {
        $this->masterRepository = $masterRepository;
    }

    public function index()
    {
        Gate::authorize('index_communication_sms_logs');
        $smsLogs = $this->masterRepository->getAllOtps();

        return view('layouts.admin.sms-logs.index', compact('smsLogs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create_communication_sms_logs');
        $emailTemplates = EmailTemplate::all();
        return view('layouts.admin.sms-logs.create', compact('emailTemplates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create_communication_sms_logs');
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

        return redirect()->route('admin.sms-logs.index')
            ->with('success', 'Email log created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        Gate::authorize('read_communication_sms_logs');
        $emailLog = EmailLog::with('emailTemplate')
            ->findOrFail($id);

        return view('layouts.admin.sms-logs.show', compact('emailLog'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        Gate::authorize('update_communication_sms_logs');
        $emailLog = EmailLog::findOrFail($id);
        $emailTemplates = EmailTemplate::all();

        return view('layouts.admin.sms-logs.edit', compact('emailLog', 'emailTemplates'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Gate::authorize('update_communication_sms_logs');
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

        return redirect()->route('admin.sms-logs.index')
            ->with('success', 'Email log updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Gate::authorize('delete_communication_sms_logs');
        $emailLog = EmailLog::findOrFail($id);
        $emailLog->delete();

        return redirect()->route('admin.sms-logs.index')
            ->with('success', 'Email log deleted successfully.');
    }
}
