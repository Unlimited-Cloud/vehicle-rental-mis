<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;

class DashboardController extends Controller
{
    public function index()
    {
        $totalVehicles = Vehicle::count();
        $availableVehicles = Vehicle::where('status', 1)->count();
        $unavailableVehicles = Vehicle::where('status', 0)->count();

        return view('layouts.admin.dashboard', compact(
            'totalVehicles',
            'availableVehicles',
            'unavailableVehicles'
        ));
    }
}
