<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMode;

class PaymentModeController extends Controller
{
    public function index()
    {
        $paymentModes = PaymentMode::latest()->get();
        return view('layouts.admin.payment-modes.index', compact('paymentModes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('layouts.admin.payment-modes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'required|boolean'
        ]);

        $logoName = null;

        if ($request->hasFile('logo')) {

            $logo = $request->file('logo');

            $logoName = time() . '.' . $logo->getClientOriginalExtension();

            $logo->move(public_path('uploads/payment_modes'), $logoName);
        }

        PaymentMode::create([
            'name' => $request->name,
            'logo' => $logoName,
            'status' => $request->status
        ]);

        return redirect()->route('admin.payment-modes.index')
            ->with('success', 'Payment Mode created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $paymentMode = PaymentMode::findOrFail($id);

        return view('layouts.admin.payment-modes.show', compact('paymentMode'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $paymentMode = PaymentMode::findOrFail($id);

        return view('layouts.admin.payment-modes.create', compact('paymentMode'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $paymentMode = PaymentMode::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'required|boolean'
        ]);

        $logoName = $paymentMode->logo;

        if ($request->hasFile('logo')) {

            // delete old logo
            if (
                $paymentMode->logo &&
                file_exists(public_path('uploads/payment_modes/' . $paymentMode->logo))
            ) {
                unlink(public_path('uploads/payment_modes/' . $paymentMode->logo));
            }

            $logo = $request->file('logo');

            $logoName = time() . '.' . $logo->getClientOriginalExtension();

            $logo->move(public_path('uploads/payment_modes'), $logoName);
        }

        $paymentMode->update([
            'name' => $request->name,
            'logo' => $logoName,
            'status' => $request->status
        ]);

        return redirect()->route('admin.payment-modes.index')
            ->with('success', 'Payment Mode updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $paymentMode = PaymentMode::findOrFail($id);

        if (
            $paymentMode->logo &&
            file_exists(public_path('uploads/payment_modes/' . $paymentMode->logo))
        ) {
            unlink(public_path('uploads/payment_modes/' . $paymentMode->logo));
        }

        $paymentMode->delete();

        return redirect()->route('admin.payment-modes.index')
            ->with('success', 'Payment Mode deleted successfully');
    }
}
