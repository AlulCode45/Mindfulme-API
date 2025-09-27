<?php

namespace App\Http\Controllers\BundlePackage;

use App\Contracts\Interfaces\BundlePackageInterface;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\BundlePackageRequest;
use Illuminate\Http\Request;

class BundlePackagesController extends Controller
{
    public BundlePackageInterface $bundlePackage;
    public function __construct(BundlePackageInterface $bundlePackageInterface)
    {
        $this->bundlePackage = $bundlePackageInterface;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ResponseHelper::success($this->bundlePackage->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BundlePackageRequest $request)
    {
        try {
            $data = $request->validated();
            $bundle = $this->bundlePackage->store($data);
            return ResponseHelper::success($bundle, 'Bundle package created successfully', 201);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to create bundle package', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = $this->bundlePackage->show($id);
            return ResponseHelper::success($data);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to retrieve bundle package', 404, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BundlePackageRequest $request, string $uuid)
    {
        try {
            $data = $request->validated();
            $bundle = $this->bundlePackage->update($data, $uuid);
            return ResponseHelper::success($this->bundlePackage->show($uuid), 'Bundle package updated successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to update bundle package', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        try {
            $this->bundlePackage->delete($uuid);
            return ResponseHelper::success([], 'Bundle package deleted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to delete bundle package', 500, $e->getMessage());
        }
    }
}
