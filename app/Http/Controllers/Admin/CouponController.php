<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\PetrolPump;
use App\Models\VehicleBooking;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Coupon::latest()->get();
        return view('layouts.admin.coupons.index', compact('items'));
    }

    public function create()
    {
        $petrolPumps = PetrolPump::all();
        $bookings = VehicleBooking::all();
        return view('layouts.admin.coupons.create', compact('petrolPumps', 'bookings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'petrol_pump_id' => 'required',
            'amount' => 'required|numeric',
            'booking_id' => 'nullable'
        ]);

        Coupon::create($request->all());

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon created successfully');
    }

    public function show(Coupon $coupon)
    {
        return view('admin.coupons.show', compact('coupon'));
    }

    public function edit(Coupon $coupon)
    {
        $petrolPumps = PetrolPump::all();
        $bookings = VehicleBooking::all();
        return view('layouts.admin.coupons.create', compact('coupon', 'petrolPumps', 'bookings'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'petrol_pump_id' => 'required',
            'amount' => 'required|numeric',
            'booking_id' => 'nullable'
        ]);

        $coupon->update($request->all());

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon updated successfully');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon deleted successfully');
    }

    public function downloadPdf(Coupon $coupon)
    {
        $coupon->load('petrolPump'); // relation

        $pdf = Pdf::loadView('layouts.admin.coupons.pdf', compact('coupon'))
            ->setPaper('A5', 'landscape');

        return $pdf->download($coupon->coupon_number . '.pdf');
    }

    public function storeFromBooking(Request $request)
    {
        // Validate the input
        $validated = $request->validate([
            'petrol_pump_id' => 'required|exists:petrol_pumps,id',
            'amount' => 'required|numeric|min:0',
            'booking_id' => 'required|exists:vehicle_bookings,id',
        ]);

        // Create coupon
        $coupon = Coupon::create($validated);

        return redirect()->back()
            ->with('success', "Coupon {$coupon->coupon_number} generated successfully!");
    }
}
