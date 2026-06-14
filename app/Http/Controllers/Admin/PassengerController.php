<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Passenger;


class PassengerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $passengers = Passenger::latest()->get();
        return view('layouts.admin.passengers.index', compact('passengers'));
    }

    public function create()
    {
        return view('layouts.admin.passengers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'contact_person' => 'required|string|max:255',
            'contact_address' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_number' => 'required|string|max:20',
            'customer_id' => 'nullable|exists:customers,id',
            'booking_id' => 'required|exists:bookings,id',
        ]);

        Passenger::create($request->all());

        return redirect()->route('layouts.admin.passengers.index')->with('success', 'Passenger created successfully.');
    }

    public function show(Passenger $passenger)
    {
        return view('layouts.admin.passengers.show', compact('passenger'));
    }

    public function edit(Passenger $passenger)
    {
        return view('layouts.admin.passengers.edit', compact('passenger'));
    }

    public function update(Request $request, Passenger $passenger)
    {
        $request->validate([
            'contact_person' => 'required|string|max:255',
            'contact_address' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_number' => 'required|string|max:20',
            'customer_id' => 'nullable|exists:customers,id',
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $passenger->update($request->all());

        return redirect()->route('layouts.admin.passengers.index')->with('success', 'Passenger updated successfully.');
    }

    public function destroy(Passenger $passenger)
    {
        $passenger->delete();
        return redirect()->route('layouts.admin.passengers.index')->with('success', 'Passenger deleted successfully.');
    }
}
