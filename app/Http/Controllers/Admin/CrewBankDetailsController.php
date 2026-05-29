<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
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
        $banks = Bank::where('is_payee_account', 1)
            ->orderBy('bank_name')
            ->get();

        return view('layouts.admin.bank_details.create', compact('crew', 'banks'));
    }

    public function store(Request $request, $crewId)
    {
        $request->validate([
            'bank_name' => 'required|string',
            // 'bank_code' => 'required|string',
            'account_holder_name' => 'required|string',
            'account_number' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->is_default == 1) {
            CrewBankDetail::where('crew_id', $crewId)
                ->update(['is_default' => 0]);
        }

        $bank = Bank::where('bank_name', $request->bank_name)->first();

        if (!$bank) {
            return back()->withErrors([
                'bank_name' => 'Selected bank not found.'
            ])->withInput();
        }

        CrewBankDetail::create([
            'crew_id' => $crewId,
            'bank_name' => $bank->bank_name,
            'bank_code' => $bank->swift_code,
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
        $banks = Bank::where('is_payee_account', 1)
            ->orderBy('bank_name')
            ->get();

        return view('layouts.admin.bank_details.create', compact('crew', 'bankDetail', 'banks'));
    }

    public function update(Request $request, $crewId, $id)
    {
        $request->validate([
            'bank_name' => 'required|string',
            // 'bank_code' => 'required|string',
            'account_holder_name' => 'required|string',
            'account_number' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $bankDetail = CrewBankDetail::findOrFail($id);

        $bank = Bank::where('bank_name', $request->bank_name)->first();

        if (!$bank) {
            return back()->withErrors([
                'bank_name' => 'Selected bank not found.'
            ])->withInput();
        }

        // if ($request->is_active == 1) {
        //     CrewBankDetail::where('crew_id', $crewId)
        //         ->update(['is_active' => 0]);
        // }

        $bankDetail->update([
            'bank_name' => $bank->bank_name,
            'bank_code' => $bank->swift_code,
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
