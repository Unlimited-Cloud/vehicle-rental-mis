<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Questionnaire;
use Illuminate\Http\Request;

class QuestionnaireController extends Controller
{
    public function index()
    {
        $questionnaires = Questionnaire::orderBy('sort_order')->latest()->paginate(15);

        return view('layouts.admin.questionnaires.index', compact('questionnaires'));
    }

    public function create()
    {
        return view('layouts.admin.questionnaires.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'type' => 'required',
        ]);

        Questionnaire::create([
            'question' => $request->question,
            'type' => $request->type,
            'is_required' => $request->is_required ?? 0,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->is_active ?? 1,
        ]);

        return redirect()
            ->route('admin.questionnaires.index')
            ->with('success', 'Question created successfully');
    }

    public function edit(Questionnaire $questionnaire)
    {
        return view('layouts.admin.questionnaires.create', compact('questionnaire'));
    }

    public function update(Request $request, Questionnaire $questionnaire)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'type' => 'required',
        ]);

        $questionnaire->update([
            'question' => $request->question,
            'type' => $request->type,
            'is_required' => $request->is_required ?? 0,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->is_active ?? 1,
        ]);

        return redirect()
            ->route('admin.questionnaires.index')
            ->with('success', 'Question updated successfully');
    }

    public function destroy(Questionnaire $questionnaire)
    {
        $questionnaire->delete();

        return redirect()->route('admin.questionnaires.index')
            ->with('success', 'Question deleted successfully');
    }
}
