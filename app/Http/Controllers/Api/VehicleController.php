<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Faq;
use App\Models\Review;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Customer;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;

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

        $customer = Customer::where('customer_uuid', $request->customer_id)->first();

        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Customer not found',
                'data' => []
            ], 404);
        }

        $customer_id = $customer->id;
        $review = Review::create([
            'vehicle_id' => $request->vehicle_id,
            'customer_id' => $customer_id,
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

    public function SearchVehicle(Request $request)
    {
        $query = Vehicle::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('vehicle_name', 'like', "%$search%")
                    ->orWhere('brand', 'like', "%$search%")
                    ->orWhere('seater', 'like', "%$search%")
                    ->orWhere('fuel_type', 'like', "%$search%");
            });
        }

        if ($request->filled('type')) {
            $query->where('vehicle_type', 'like', '%' . $request->type . '%');
        }

        if ($request->filled('fueltype')) {
            $query->where('fuel_type', 'like', '%' . $request->fueltype . '%');
        }

        if ($request->filled('brand')) {
            $query->where('brand', 'like', '%' . $request->brand . '%');
        }

        if ($request->filled('seater')) {
            $query->where('seater', 'like', '%' . $request->seater . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $allowedSortFields = ['vehicle_name', 'brand', 'created_at'];

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }

        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortOrder);

        $query->orderBy($sortBy, $sortOrder);

        $pageSize = $request->input('page_size', 20);

        return response()->json(
            $query->paginate($pageSize)
        );
    }



    public function faq()
    {
        $faqs = Faq::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->latest()
            ->get([
                'id',
                'question',
                'answer',
            ]);

        return response()->json([
            'success' => true,
            'message' => 'FAQ list fetched successfully',
            'data' => $faqs
        ]);
    }

    public function downloadInsuranceDocument($vehicle_id)
    {
        $vehicle = Vehicle::where('id', $vehicle_id)->first();

        if (!$vehicle) {
            return response()->json([
                'status' => false,
                'message' => 'Vehicle not found.'
            ], 404);
        }

        if (empty($vehicle->insurance_policy_document)) {
            return response()->json([
                'status' => false,
                'message' => 'Insurance document not found.'
            ], 404);
        }

        $path = public_path($vehicle->insurance_policy_document);

        if (!File::exists($path)) {
            return response()->json([
                'status' => false,
                'message' => 'File does not exist.'
            ], 404);
        }

        $fileName = basename($path);

        return response()->download($path, $fileName);
    }
}
