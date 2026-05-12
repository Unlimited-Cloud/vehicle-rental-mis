<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::latest()->get();

        return view('layouts.admin.faq.index', compact('faqs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('layouts.admin.faq.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        Faq::create([
            'question' => $request->question,
            'answer' => $request->answer,
            'is_active' => $request->is_active ?? 1,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.faq.index')
            ->with('success', 'FAQ created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $faq = Faq::findOrFail($id);

        return view('layouts.admin.faq.show', compact('faq'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $faq = Faq::findOrFail($id);

        return view('layouts.admin.faq.create', compact('faq'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $faq = Faq::findOrFail($id);

        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $faq->update([
            'question' => $request->question,
            'answer' => $request->answer,
            'is_active' => $request->is_active ?? 1,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.faq.index')
            ->with('success', 'FAQ updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $faq = Faq::findOrFail($id);

        $faq->delete();

        return redirect()->route('admin.faq.index')
            ->with('success', 'FAQ deleted successfully');
    }
}
