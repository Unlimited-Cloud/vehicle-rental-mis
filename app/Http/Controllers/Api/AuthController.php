<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CustomerService;
use App\Services\AuthService;
use App\Utilities\VehicleRentalUtilities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $customerService;
    protected $authService;

    public function __construct(
        CustomerService $customerService,
        AuthService $authService
    ) {
        $this->customerService = $customerService;
        $this->authService = $authService;
    }

    public function getOtpPasscodeAppLogin(Request $request)
    {
        $data = $this->authService->getOtpPasscodeAppLogin($request);
        return VehicleRentalUtilities::jsonResponse($data);
    }

    public function login(Request $request)
    {
        $data = $this->authService->appLogin($request);
        return VehicleRentalUtilities::jsonResponse($data);
    }
}
