<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Review;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use Carbon\Carbon;

class VehicleController extends Controller
{
    public function storeReview(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'customer_id' => 'nullable',
            'rating' => 'required|integer|min:1|max:5',
            'description' => 'nullable|string',
        ]);


        $review = Review::create([
            'vehicle_id' => $request->vehicle_id,
            'customer_id' => $request->customer_id,
            'rating' => $request->rating,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review added successfully',
            'review' => $review
        ], 201);
    }

    // Get all reviews for a vehicle
    public function getReviews($vehicle_id)
    {
        $vehicle = Vehicle::find($vehicle_id);

        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found'
            ], 404);
        }

        $reviews = Review::with('customer')
            ->where('vehicle_id', $vehicle_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'vehicle' => [
                'id' => $vehicle->id,
                'name' => $vehicle->vehicle_name
            ],
            'reviews' => $reviews
        ]);
    }


    public function getBanner()
    {
        $now = Carbon::now();

        $banners = Banner::where('is_active', 1)
            ->where(function ($query) use ($now) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            })
            ->orderBy('order', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Banner list fetched successfully',
            'data' => $banners->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'image' => asset('uploads/banners/' . $banner->image),
                    'link' => $banner->link,
                    'description' => $banner->description,
                ];
            })
        ]);
    }
}
