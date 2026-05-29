<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrewBankDetail;
use App\Models\CrewProfile;
use Illuminate\Http\Request;

class CrewBankDetailsController extends Controller
{
    public function index($crewId)
    {
        $crew = CrewProfile::findOrFail($crewId);

        $bankDetails = CrewBankDetail::where('crew_id', $crewId)
            ->latest()
            ->get();

        return view('layouts.admin.bank_details.index', compact('crew', 'bankDetails'));
    }

    public function create($crewId)
    {
        $crew = CrewProfile::findOrFail($crewId);

        return view('layouts.admin.bank_details.create', compact('crew'));
    }

    public function store(Request $request, $crewId)
    {
        $request->validate([
            'bank_name' => 'required|string',
            'bank_code' => 'required|string',
            'account_holder_name' => 'required|string',
            'account_number' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->is_default == 1) {
            CrewBankDetail::where('crew_id', $crewId)
                ->update(['is_default' => 0]);
        }

        CrewBankDetail::create([
            'crew_id' => $crewId,
            'bank_name' => $request->bank_name,
            'bank_code' => $request->bank_code,
            'account_holder_name' => $request->account_holder_name,
            'account_number' => $request->account_number,
            'is_active' => $request->is_active ?? 1,
        ]);

        return redirect()
            ->route('admin.bank-details.index', $crewId)
            ->with('success', 'Bank detail added successfully.');
    }

    public function edit($crewId, $id)
    {
        $crew = CrewProfile::findOrFail($crewId);

        $bankDetail = CrewBankDetail::findOrFail($id);

        return view('layouts.admin.bank_details.create', compact('crew', 'bankDetail'));
    }

    public function update(Request $request, $crewId, $id)
    {
        $request->validate([
            'bank_name' => 'required|string',
            'bank_code' => 'required|string',
            'account_holder_name' => 'required|string',
            'account_number' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $bankDetail = CrewBankDetail::findOrFail($id);

        // if ($request->is_active == 1) {
        //     CrewBankDetail::where('crew_id', $crewId)
        //         ->update(['is_active' => 0]);
        // }

        $bankDetail->update([
            'bank_name' => $request->bank_name,
            'bank_code' => $request->bank_code,
            'account_holder_name' => $request->account_holder_name,
            'account_number' => $request->account_number,
            'is_active' => $request->is_active ?? 1,
        ]);

        return redirect()
            ->route('admin.bank-details.index', $crewId)
            ->with('success', 'Bank detail updated successfully.');
    }

    public function destroy($crewId, $id)
    {
        $bankDetail = CrewBankDetail::findOrFail($id);

        $bankDetail->delete();

        return redirect()
            ->route('admin.bank-details.index', $crewId)
            ->with('success', 'Bank detail deleted successfully.');
    }
}
