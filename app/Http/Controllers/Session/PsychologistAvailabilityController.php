<?php

namespace App\Http\Controllers\Session;

use App\Contracts\Interfaces\PsychologistAvailabilityInterface;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PsychologistAvailabilityController extends Controller
{
    private PsychologistAvailabilityInterface $availability;

    public function __construct(PsychologistAvailabilityInterface $availability)
    {
        $this->availability = $availability;
        $this->middleware('auth:sanctum');
        $this->middleware('role:psychologist|superadmin')->except(['getAvailableTimeSlots', 'checkTimeSlotAvailability']);
    }

    public function index(): JsonResponse
    {
        $user = auth()->user();

        if ($user->hasRole('psychologist')) {
            $availabilities = $this->availability->getByPsychologistId($user->uuid);
        } else {
            $availabilities = $this->availability->get();
        }

        return ResponseHelper::success($availabilities, 'Psychologist availabilities retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_available' => 'boolean',
            'break_periods' => 'array',
            'break_periods.*.start' => 'required|date_format:H:i',
            'break_periods.*.end' => 'required|date_format:H:i|after:break_periods.*.start',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'notes' => 'nullable|string'
        ]);

        try {
            $validated['psychologist_id'] = auth()->user()->uuid;

            $availability = $this->availability->store($validated);

            return ResponseHelper::success($availability, 'Availability created successfully', 201);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $availability = $this->availability->show($id);

            // Check if the user owns this availability or is admin
            $user = auth()->user();
            if (!$user->hasRole('superadmin') &&
                $availability->psychologist_id !== $user->uuid) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            return ResponseHelper::success($availability, 'Availability retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 404);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'day_of_week' => 'sometimes|required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i|after:start_time',
            'is_available' => 'sometimes|boolean',
            'break_periods' => 'sometimes|array',
            'break_periods.*.start' => 'required|date_format:H:i',
            'break_periods.*.end' => 'required|date_format:H:i|after:break_periods.*.start',
            'effective_from' => 'sometimes|nullable|date',
            'effective_to' => 'sometimes|nullable|date|after:effective_from',
            'notes' => 'sometimes|nullable|string'
        ]);

        try {
            $existingAvailability = $this->availability->show($id);

            // Check if the user owns this availability
            $user = auth()->user();
            if (!$user->hasRole('superadmin') &&
                $existingAvailability->psychologist_id !== $user->uuid) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            $availability = $this->availability->update($id, $validated);

            return ResponseHelper::success($availability, 'Availability updated successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 404);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $existingAvailability = $this->availability->show($id);

            // Check if the user owns this availability
            $user = auth()->user();
            if (!$user->hasRole('superadmin') &&
                $existingAvailability->psychologist_id !== $user->uuid) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            $this->availability->delete($id);

            return ResponseHelper::success([], 'Availability deleted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 404);
        }
    }

    public function getAvailableTimeSlots(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'psychologist_id' => 'required|exists:users,uuid',
            'date' => 'required|date|after_or_equal:today',
            'duration_minutes' => 'required|integer|min:15|max:480'
        ]);

        try {
            $availableSlots = $this->availability->getAvailableTimeSlots(
                $validated['psychologist_id'],
                $validated['date'],
                $validated['duration_minutes']
            );

            return ResponseHelper::success($availableSlots, 'Available time slots retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function checkTimeSlotAvailability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'psychologist_id' => 'required|exists:users,uuid',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time'
        ]);

        try {
            $isAvailable = $this->availability->checkTimeSlotAvailability(
                $validated['psychologist_id'],
                $validated['date'],
                $validated['start_time'],
                $validated['end_time']
            );

            return ResponseHelper::success([
                'available' => $isAvailable,
                'message' => $isAvailable ? 'Time slot is available' : 'Time slot is not available'
            ], 'Time slot availability checked');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function getMyAvailability(): JsonResponse
    {
        try {
            $availabilities = $this->availability->getActiveAvailability(auth()->user()->uuid);

            return ResponseHelper::success($availabilities, 'My availabilities retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}