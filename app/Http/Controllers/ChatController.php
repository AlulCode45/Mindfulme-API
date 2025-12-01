<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ChatMessages;
use App\Models\Appointments;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get chat messages for a specific session or conversation
     */
    public function getMessages(Request $request, $sessionId = null): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => 'nullable|string',
            'session_id' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        try {
            $user = auth()->user();
            $query = ChatMessages::query();

            // Filter by conversation or session
            if (!empty($sessionId)) {
                $query->where(function ($q) use ($user, $sessionId) {
                    $q->where(function ($subQuery) use ($user, $sessionId) {
                        $subQuery->where('sender_id', $user->uuid)
                              ->orWhere('receiver_id', $user->uuid);
                    })
                    ->where('message', 'like', "%session_id:{$sessionId}%");
                });
            } elseif (!empty($validated['conversation_id'])) {
                // Add conversation filtering logic when conversation system is implemented
                $query->where(function ($q) use ($user, $validated) {
                    $q->where('sender_id', $user->uuid)
                      ->orWhere('receiver_id', $user->uuid);
                });
            } else {
                // Get all messages for the current user
                $query->where(function ($q) use ($user) {
                    $q->where('sender_id', $user->uuid)
                      ->orWhere('receiver_id', $user->uuid);
                });
            }

            $messages = $query->with(['sender:uuid,name,email,avatar', 'receiver:uuid,name,email,avatar'])
                ->orderBy('created_at', 'desc')
                ->paginate($validated['per_page'] ?? 50);

            return ResponseHelper::success($messages, 'Chat messages retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Send a new message
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,uuid',
            'message' => 'required|string|max:2000',
            'session_id' => 'nullable|string|max:255'
        ]);

        try {
            $user = auth()->user();

            // Create the chat message
            $chatMessage = ChatMessages::create([
                'sender_id' => $user->uuid,
                'receiver_id' => $validated['receiver_id'],
                'message' => $validated['message'],
                'session_id' => $validated['session_id'] ?? null,
            ]);

            // Load relationships for response
            $chatMessage->load(['sender:uuid,name,email,avatar', 'receiver:uuid,name,email,avatar']);

            return ResponseHelper::success($chatMessage, 'Message sent successfully', 201);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get available chat partners (counselors for users, users for counselors)
     */
    public function getChatPartners(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if ($user->hasRole('user')) {
                // Users can chat with their assigned counselors
                $partners = User::whereHas('roles', function ($query) {
                    $query->where('name', 'like', '%psychologist%')
                          ->orWhere('name', 'like', '%counselor%');
                })
                ->whereNotIn('uuid', [$user->uuid])
                ->with('detail')
                ->select('uuid', 'name', 'email', 'photo', 'status')
                ->get()
                ->map(function ($psychologist) {
                    return [
                        'id' => $psychologist->uuid,
                        'name' => $psychologist->name,
                        'email' => $psychologist->email,
                        'avatar' => $psychologist->detail?->photo ?? null,
                        'title' => $psychologist->detail?->specialization ?? 'Psikolog',
                        'status' => 'online', // This could be enhanced with real online status
                    ];
                });
            } else {
                // Psychologists and admins can chat with their assigned users
                $partners = User::whereHasRole('user')
                    ->whereNotIn('uuid', [$user->uuid])
                    ->with('detail')
                    ->select('uuid', 'name', 'email', 'photo', 'status')
                    ->get()
                    ->map(function ($user) {
                        return [
                            'id' => $user->uuid,
                            'name' => $user->name,
                            'email' => $user->email,
                            'avatar' => $user->detail?->photo ?? null,
                            'title' => 'Pasien',
                            'status' => 'online', // This could be enhanced with real online status
                        ];
                    });
            }

            return ResponseHelper::success($partners, 'Chat partners retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get chat sessions/conversations
     */
    public function getChatSessions(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            // For now, we'll use appointments as chat sessions
            if ($user->hasRole('user')) {
                $sessions = Appointments::with(['psychologist:uuid,name,email'])
                    ->where('user_id', $user->uuid)
                    ->whereIn('status', ['scheduled', 'in_progress'])
                    ->orderBy('start_time', 'asc')
                    ->get()
                    ->map(function ($appointment) {
                        return [
                            'id' => $appointment->appointment_id,
                            'psychologist_id' => $appointment->psychologist_id,
                            'psychologist_name' => $appointment->psychologist?->name ?? 'Unknown',
                            'psychologist_email' => $appointment->psychologist?->email ?? '',
                            'start_time' => $appointment->start_time,
                            'status' => $appointment->status,
                            'has_active_chat' => ChatMessages::where('session_id', $appointment->appointment_id)->exists()
                        ];
                    });
            } else {
                // For counselors and admins, get their assigned users
                $sessions = Appointments::with(['user:uuid,name,email'])
                    ->where('psychologist_id', $user->uuid)
                    ->whereIn('status', ['scheduled', 'in_progress'])
                    ->orderBy('start_time', 'asc')
                    ->get()
                    ->map(function ($appointment) {
                        return [
                            'id' => $appointment->appointment_id,
                            'user_id' => $appointment->user_id,
                            'user_name' => $appointment->user?->name ?? 'Unknown',
                            'user_email' => $appointment->user?->email ?? '',
                            'start_time' => $appointment->start_time,
                            'status' => $appointment->status,
                            'has_active_chat' => ChatMessages::where('session_id', $appointment->appointment_id)->exists()
                        ];
                    });
            }

            return ResponseHelper::success($sessions, 'Chat sessions retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'string|exists:chat_messages,chat_message_id'
        ]);

        try {
            $user = auth()->user();

            $updated = ChatMessages::whereIn('chat_message_id', $validated['message_ids'])
                ->where('receiver_id', $user->uuid)
                ->update(['read_at' => now()]);

            return ResponseHelper::success($updated, 'Messages marked as read');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get unread message count
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            $unreadCount = ChatMessages::where('receiver_id', $user->uuid)
                ->whereNull('read_at')
                ->count();

            return ResponseHelper::success(['unread_count' => $unreadCount], 'Unread messages count retrieved');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Update chat message (edit or recall)
     */
    public function updateMessage(Request $request, string $messageId): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'sometimes|string|max:2000',
            'is_edited' => 'sometimes|boolean',
            'is_recalled' => 'sometimes|boolean',
        ]);

        try {
            $user = auth()->user();
            $message = ChatMessages::findOrFail($messageId);

            // Check if user can modify this message
            if ($message->sender_id !== $user->uuid && !$user->hasRole('superadmin')) {
                return ResponseHelper::error('Unauthorized to modify this message', 403);
            }

            $message->update($validated);

            return ResponseHelper::success($message, 'Message updated successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Delete a chat message
     */
    public function deleteMessage(string $messageId): JsonResponse
    {
        try {
            $user = auth()->user();
            $message = ChatMessages::findOrFail($messageId);

            // Check if user can delete this message
            if ($message->sender_id !== $user->uuid && !$user->hasRole('superadmin')) {
                return ResponseHelper::error('Unauthorized to delete this message', 403);
            }

            $message->delete();

            return ResponseHelper::success(null, 'Message deleted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}