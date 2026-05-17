<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactUs;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{
    public function index()
    {
        $contacts = ContactUs::latest()->get();

        return view('layouts.admin.contact-us.index', compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('layouts.admin.contact-us.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required',
            'email' => 'required|email',
            'mobile_number' => 'required',
            'facebook_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'tiktok_url' => 'nullable|url',
            'youtube_url' => 'nullable|url',
            'whatsapp_number' => 'nullable',
            'subject' => 'nullable',
            'message' => 'nullable',
            'address' => 'nullable',
            'website_url' => 'nullable|url',
        ]);

        ContactUs::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'facebook_url' => $request->facebook_url,
            'instagram_url' => $request->instagram_url,
            'linkedin_url' => $request->linkedin_url,
            'tiktok_url' => $request->tiktok_url,
            'twitter_url' => $request->twitter_url,
            'youtube_url' => $request->youtube_url,
            'whatsapp_number' => $request->whatsapp_number,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => $request->status ?? 'active',
            'address' => $request->address,
            'website_url' => $request->website_url,
        ]);

        return redirect()->route('admin.contact-us.index')
            ->with('success', 'Contact created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $contact = ContactUs::findOrFail($id);

        return view('layouts.admin.contact-us.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $contact = ContactUs::findOrFail($id);

        return view('layouts.admin.contact-us.create', compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $contact = ContactUs::findOrFail($id);

        $request->validate([
            'full_name' => 'required',
            'email' => 'required|email',
            'mobile_number' => 'required',
            'facebook_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'tiktok_url' => 'nullable|url',
            'youtube_url' => 'nullable|url',
            'whatsapp_number' => 'nullable',
            'subject' => 'nullable',
            'message' => 'nullable',
            'address' => 'nullable',
            'website_url' => 'nullable|url',
        ]);

        $contact->update([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'facebook_url' => $request->facebook_url,
            'instagram_url' => $request->instagram_url,
            'linkedin_url' => $request->linkedin_url,
            'twitter_url' => $request->twitter_url,
            'tiktok_url' => $request->tiktok_url,
            'youtube_url' => $request->youtube_url,
            'whatsapp_number' => $request->whatsapp_number,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => $request->status ?? 'active',
            'address' => $request->address,
            'website_url' => $request->website_url,
        ]);

        return redirect()->route('admin.contact-us.index')
            ->with('success', 'Contact updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $contact = ContactUs::findOrFail($id);

        $contact->delete();

        return redirect()->route('admin.contact-us.index')
            ->with('success', 'Contact deleted successfully');
    }
}
