<?php

namespace App\Http\Controllers\Session;

use App\Contracts\Interfaces\SessionTypeInterface;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SessionTypeController extends Controller
{
    private SessionTypeInterface $sessionType;

    public function __construct(SessionTypeInterface $sessionType)
    {
        $this->sessionType = $sessionType;
        $this->middleware('auth:sanctum');
        $this->middleware('role:superadmin')->except(['index', 'show']);
    }

    public function index(): JsonResponse
    {
        try {
            $sessionTypes = $this->sessionType->getActive();

            return ResponseHelper::success($sessionTypes, 'Session types retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'price' => 'required|numeric|min:0|max:999999.99',
            'consultation_type' => 'required|in:individual,couples,family,group',
            'color' => 'nullable|string|max:7', // Hex color code
            'max_participants' => 'nullable|integer|min:1|max:100',
            'requirements' => 'nullable|string|max:1000'
        ]);

        try {
            $sessionType = $this->sessionType->store($validated);

            return ResponseHelper::success($sessionType, 'Session type created successfully', 201);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $sessionType = $this->sessionType->show($id);

            return ResponseHelper::success($sessionType, 'Session type retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 404);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:1000',
            'duration_minutes' => 'sometimes|required|integer|min:15|max:480',
            'price' => 'sometimes|required|numeric|min:0|max:999999.99',
            'consultation_type' => 'sometimes|required|in:individual,couples,family,group',
            'color' => 'sometimes|nullable|string|max:7',
            'max_participants' => 'sometimes|nullable|integer|min:1|max:100',
            'requirements' => 'sometimes|nullable|string|max:1000',
            'is_active' => 'sometimes|boolean'
        ]);

        try {
            $sessionType = $this->sessionType->update($id, $validated);

            return ResponseHelper::success($sessionType, 'Session type updated successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 404);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->sessionType->delete($id);

            return ResponseHelper::success([], 'Session type deleted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 404);
        }
    }

    public function getByConsultationType(string $type): JsonResponse
    {
        if (!in_array($type, ['individual', 'couples', 'family', 'group'])) {
            return ResponseHelper::error('Invalid consultation type', 400);
        }

        try {
            $sessionTypes = $this->sessionType->getByConsultationType($type);

            return ResponseHelper::success($sessionTypes, "Session types for {$type} retrieved successfully");
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}