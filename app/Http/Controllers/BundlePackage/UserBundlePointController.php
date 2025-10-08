<?php

namespace App\Http\Controllers\BundlePackage;

use App\Contracts\Interfaces\BundlePackageInterface;
use App\Contracts\Interfaces\UserBundlePointInterface;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserBundlePointController extends Controller
{
    public UserBundlePointInterface $userBundlePoint;
    public BundlePackageInterface $bundlePackage;

    public function __construct(
        UserBundlePointInterface $userBundlePoint,
        BundlePackageInterface $bundlePackage
    ) {
        $this->userBundlePoint = $userBundlePoint;
        $this->bundlePackage = $bundlePackage;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $data = $this->userBundlePoint->getUserBundlePoints($user->uuid);

        return ResponseHelper::success($data, 'Success get user bundle point');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            $bundlePackageData = $this->bundlePackage->show($request->bundle_package_id);
            $data = [
                'user_id' => $user->uuid,
                'bundle_package_id' => $request->bundle_package_id,
                'bundle_points' => $bundlePackageData->points,
                'current_points' => $bundlePackageData->points,
                'purchase_at' => Carbon::now()
            ];
            $this->userBundlePoint->store($data);

            return ResponseHelper::success($data, 'Success store data');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->userBundlePoint->delete($id);

            return ResponseHelper::success([], 'Success delete data');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
