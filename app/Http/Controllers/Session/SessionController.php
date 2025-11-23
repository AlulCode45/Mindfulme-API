<?php

namespace App\Http\Controllers\Session;

use App\Contracts\Interfaces\SessionTypeInterface;
use App\Contracts\Interfaces\PsychologistAvailabilityInterface;
use App\Contracts\Interfaces\ComplaintInterface;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Appointments;
use App\Enums\AppointmentStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    private SessionTypeInterface $sessionType;
    private PsychologistAvailabilityInterface $availability;
    private ComplaintInterface $complaint;

    public function __construct(
        SessionTypeInterface $sessionType,
        PsychologistAvailabilityInterface $availability,
        ComplaintInterface $complaint
    ) {
        $this->sessionType = $sessionType;
        $this->availability = $availability;
        $this->complaint = $complaint;
        $this->middleware('auth:sanctum');
    }

    public function getSessionTypes(): JsonResponse
    {
        try {
            $sessionTypes = $this->sessionType->getActive();

            return ResponseHelper::success($sessionTypes, 'Session types retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function bookSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'psychologist_id' => 'required|exists:users,uuid',
            'session_type_id' => 'required|exists:session_types,session_type_id',
            'start_time' => 'required|date|after:now',
            'session_title' => 'sometimes|required|string|max:255',
            'session_description' => 'nullable|string|max:1000',
            'user_notes' => 'nullable|string|max:1000',
            'complaint_id' => 'nullable|exists:complaints,complaint_id',
            'user_id' => 'sometimes|exists:users,uuid', // Allow psychologist to specify user_id
            'participants' => 'nullable|array',
            'participants.*.name' => 'required|string|max:255',
            'participants.*.email' => 'required|email|max:255',
            'participants.*.phone' => 'nullable|string|max:20'
        ]);

        try {
            DB::beginTransaction();

            // Get session type details
            $sessionType = $this->sessionType->show($validated['session_type_id']);
            $startTime = \Carbon\Carbon::parse($validated['start_time']);
            $endTime = $startTime->copy()->addMinutes($sessionType->duration_minutes);
            $date = $startTime->format('Y-m-d');
            $timeStart = $startTime->format('H:i');
            $timeEnd = $endTime->format('H:i');

            // Check if the time slot is available
            if (!$this->availability->checkTimeSlotAvailability(
                $validated['psychologist_id'],
                $date,
                $timeStart,
                $timeEnd
            )) {
                throw new \Exception('Selected time slot is not available');
            }

            // Determine the user_id for the appointment
            $user = auth()->user();
            $targetUserId = $validated['user_id'] ?? $user->uuid;

            // Handle complaint_id - if not provided, create a default complaint
            $complaintId = $validated['complaint_id'] ?? null;

            if (isset($validated['complaint_id'])) {
                // Check if user owns the complaint (if provided)
                $complaint = $this->complaint->show($validated['complaint_id']);
                if ($complaint->user_id !== $targetUserId) {
                    throw new \Exception('You can only book sessions for your own complaints');
                }
            } else {
                // Create a default complaint for psychologist-initiated bookings
                $defaultComplaintData = [
                    'user_id' => $targetUserId,
                    'title' => 'Sesi Konseling Psikolog',
                    'description' => 'Sesi konseling yang dijadwalkan oleh psikolog',
                    'category' => 'general',
                    'chronology' => 'Sesi konseling langsung dengan psikolog',
                    'status' => 'new'
                ];

                $defaultComplaint = $this->complaint->store($defaultComplaintData);
                $complaintId = $defaultComplaint->complaint_id;
            }

            // Create the appointment
            $appointmentData = [
                'psychologist_id' => $validated['psychologist_id'],
                'user_id' => $targetUserId,
                'session_type_id' => $validated['session_type_id'],
                'complaint_id' => $complaintId,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => AppointmentStatus::SCHEDULED,
                'session_title' => $validated['session_title'] ?? $sessionType->name,
                'session_description' => $validated['session_description'] ?? $sessionType->description,
                'user_notes' => $validated['user_notes'] ?? null,
                'price' => $sessionType->price,
                'participants' => $validated['participants'] ?? null,
                'meeting_link' => $this->generateMeetingLink()
            ];

            $appointment = Appointments::create($appointmentData);

            DB::commit();

            // Load relationships for response
            $appointment->load(['psychologist:uuid,name,email', 'user:uuid,name,email', 'sessionType']);

            return ResponseHelper::success($appointment, 'Session booked successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error($e->getMessage(), 400);
        }
    }

    public function getMySessions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:scheduled,completed,canceled',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'per_page' => 'sometimes|integer|min:1|max:100'
        ]);

        try {
            $query = Appointments::with(['psychologist:uuid,name,email', 'sessionType', 'complaint'])
                ->forUser(auth()->user()->uuid);

            // Apply filters
            if (isset($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            if (isset($validated['start_date'])) {
                $query->where('start_time', '>=', $validated['start_date']);
            }

            if (isset($validated['end_date'])) {
                $query->where('start_time', '<=', $validated['end_date'] . ' 23:59:59');
            }

            $sessions = $query->orderBy('start_time', 'desc')
                ->paginate($validated['per_page'] ?? 15);

            return ResponseHelper::success($sessions, 'My sessions retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function getPsychologistSessions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:scheduled,completed,canceled',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'per_page' => 'sometimes|integer|min:1|max:100'
        ]);

        // Check if user is psychologist or admin
        $user = auth()->user();
        if (!$user->hasRole('psychologist|superadmin')) {
            return ResponseHelper::error('Unauthorized', 403);
        }

        try {
            $query = Appointments::with(['user:uuid,name,email', 'sessionType', 'complaint'])
                ->forPsychologist($user->hasRole('psychologist') ? $user->uuid : $request->get('psychologist_id'));

            // Apply filters
            if (isset($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            if (isset($validated['start_date'])) {
                $query->where('start_time', '>=', $validated['start_date']);
            }

            if (isset($validated['end_date'])) {
                $query->where('start_time', '<=', $validated['end_date'] . ' 23:59:59');
            }

            $sessions = $query->orderBy('start_time', 'asc')
                ->paginate($validated['per_page'] ?? 15);

            return ResponseHelper::success($sessions, 'Psychologist sessions retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function getUpcomingSessions(): JsonResponse
    {
        try {
            $sessions = Appointments::with(['psychologist:uuid,name,email', 'sessionType'])
                ->forUser(auth()->user()->uuid)
                ->upcoming()
                ->orderBy('start_time', 'asc')
                ->limit(10)
                ->get();

            return ResponseHelper::success($sessions, 'Upcoming sessions retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function getSessionDetails(string $id): JsonResponse
    {
        try {
            $session = Appointments::with([
                'psychologist:uuid,name,email',
                'user:uuid,name,email',
                'sessionType',
                'complaint'
            ])->findOrFail($id);

            // Check if user has access to this session
            $user = auth()->user();
            if (!$user->hasRole('superadmin') &&
                $session->user_id !== $user->uuid &&
                $session->psychologist_id !== $user->uuid) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            return ResponseHelper::success($session, 'Session details retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 404);
        }
    }

    public function updateSessionStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,completed,canceled',
            'psychologist_notes' => 'nullable|string|max:1000',
            'cancellation_reason' => 'required_if:status,canceled|string|max:1000'
        ]);

        try {
            $session = Appointments::findOrFail($id);

            // Check permissions
            $user = auth()->user();
            $isPsychologist = $session->psychologist_id === $user->uuid && $user->hasRole('psychologist');
            $isUser = $session->user_id === $user->uuid;
            $isAdmin = $user->hasRole('superadmin');

            // Only psychologists can mark as completed
            if ($validated['status'] === AppointmentStatus::COMPLETED && !$isPsychologist && !$isAdmin) {
                return ResponseHelper::error('Only psychologists can mark sessions as completed', 403);
            }

            // Users can only cancel their own sessions, psychologists can cancel sessions assigned to them
            if ($validated['status'] === AppointmentStatus::CANCELED) {
                if (!$isUser && !$isPsychologist && !$isAdmin) {
                    return ResponseHelper::error('Unauthorized to cancel this session', 403);
                }

                // Check cancellation policy (24 hours notice)
                if ($session->start_time->diffInHours(now()) < 24 && !$isPsychologist && !$isAdmin) {
                    return ResponseHelper::error('Cannot cancel session less than 24 hours before start time', 400);
                }
            }

            // Update session
            $session->status = $validated['status'];

            if (isset($validated['psychologist_notes']) && ($isPsychologist || $isAdmin)) {
                $session->psychologist_notes = $validated['psychologist_notes'];
            }

            if ($validated['status'] === AppointmentStatus::CANCELED) {
                $session->cancellation_reason = $validated['cancellation_reason'];
                $session->canceled_at = now();
            }

            $session->save();

            return ResponseHelper::success($session, 'Session status updated successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 404);
        }
    }

    public function rescheduleSession(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'new_start_time' => 'required|date|after:now',
            'reason' => 'required|string|max:1000'
        ]);

        try {
            $session = Appointments::findOrFail($id);

            // Check permissions
            $user = auth()->user();
            $isUser = $session->user_id === $user->uuid;
            $isPsychologist = $session->psychologist_id === $user->uuid && $user->hasRole('psychologist');
            $isAdmin = $user->hasRole('superadmin');

            if (!$isUser && !$isPsychologist && !$isAdmin) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            // Check if session can be rescheduled
            if (!$session->canBeRescheduled() && !$isAdmin) {
                return ResponseHelper::error('This session cannot be rescheduled (less than 24 hours notice)', 400);
            }

            DB::beginTransaction();

            $newStartTime = \Carbon\Carbon::parse($validated['new_start_time']);
            $newEndTime = $newStartTime->copy()->addMinutes($session->getDurationInMinutes());
            $date = $newStartTime->format('Y-m-d');
            $timeStart = $newStartTime->format('H:i');
            $timeEnd = $newEndTime->format('H:i');

            // Check new availability
            if (!$this->availability->checkTimeSlotAvailability(
                $session->psychologist_id,
                $date,
                $timeStart,
                $timeEnd
            )) {
                throw new \Exception('New time slot is not available');
            }

            // Update session
            $session->start_time = $newStartTime;
            $session->end_time = $newEndTime;
            $session->meeting_link = $this->generateMeetingLink(); // Generate new meeting link

            // Add reschedule notes
            $notes = "Rescheduled by " . $user->name . " (" . now()->format('Y-m-d H:i') . ")\n";
            $notes .= "Reason: " . $validated['reason'] . "\n";

            if ($isPsychologist || $isAdmin) {
                $session->psychologist_notes = ($session->psychologist_notes ?? '') . $notes;
            } else {
                $session->user_notes = ($session->user_notes ?? '') . $notes;
            }

            $session->save();

            DB::commit();

            return ResponseHelper::success($session, 'Session rescheduled successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error($e->getMessage(), 400);
        }
    }

    private function generateMeetingLink(): string
    {
        // For now, generate a simple unique meeting link
        // In production, you might want to integrate with Zoom, Google Meet, etc.
        $meetingId = strtoupper(Str::random(10));
        return "https://meet.jit.si/MindfulMe-{$meetingId}";
    }
}