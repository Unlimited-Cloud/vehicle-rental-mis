<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Province;
use App\Models\District;
use App\Models\VDC;
use App\Models\Country;
use App\Repositories\Interfaces\MasterRepositoryInterface;

class ResourceController extends Controller
{
    protected $masterRepository;

    public function __construct(
        MasterRepositoryInterface $masterRepository
    ) {
        $this->masterRepository = $masterRepository;
    }
    
    public function getCountries()
    {
        $countries = $this->masterRepository->getCountries();

        return response()->json([
            'status' => true,
            'data' => $countries
        ]);
    }
    public function provinces()
    {
        $provinces = Province::select('id', 'pname', 'pnumber', 'headquarter', 'pname_np', 'status', 'map_index')
            ->orderBy('pnumber', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $provinces
        ]);
    }

    public function districtsByProvince(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'province_id' => 'required|exists:province,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $districts = District::where('province_id', $request->province_id)
            ->select('id', 'name', 'province_id', 'name_np', 'district_index')
            ->orderBy('name', 'asc')
            ->get();

        if ($districts->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No districts found for this province'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $districts
        ]);
    }

    public function vdcsByDistrict(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'district_id' => 'required|exists:district,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $vdcs = VDC::where('DISTRICT_ID', $request->district_id)
            ->select('id', 'NAME')
            ->orderBy('NAME', 'asc')
            ->get();

        if ($vdcs->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No VDC found for this district'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $vdcs
        ]);
    }
}
