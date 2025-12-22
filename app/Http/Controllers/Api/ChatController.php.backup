<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Complaints;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function getRooms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string',
            'user_type' => 'required|in:user,admin,psychologist'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first()
            ], 400);
        }

        try {
            $userId = $request->user_id;
            $userType = $request->user_type;

            $query = Complaints::with([
                'user',
                'chats' => function ($q) {
                    $q->with('sender')->orderBy('created_at', 'desc');
                }
            ])
                ->whereNotNull('classification')
                ->whereHas('chats');

            if ($userType === 'user') {
                $query->where('user_id', $userId);
            }

            $complaints = $query->get();
            $chatRooms = [];

            foreach ($complaints as $complaint) {
                $lastMessage = $complaint->chats->first();
                $unreadCount = 0;

                if ($userType === 'admin' || $userType === 'psychologist') {
                    $unreadCount = $complaint->chats->where('sender_type', 'user')->where('is_read', false)->count();
                } else {
                    $unreadCount = $complaint->chats->where('sender_type', '!=', 'user')->where('is_read', false)->count();
                }

                $chatRooms[] = [
                    'complaint_id' => $complaint->complaint_id,
                    'complaint' => [
                        'id' => $complaint->complaint_id,
                        'title' => $complaint->title,
                        'status' => $complaint->status,
                        'classification' => $complaint->classification,
                        'user' => [
                            'id' => $complaint->user->uuid,
                            'name' => $complaint->user->name,
                            'avatar' => $complaint->user->avatar ?? null
                        ]
                    ],
                    'last_message' => $lastMessage ? [
                        'id' => $lastMessage->id,
                        'message_text' => $lastMessage->message_text,
                        'message_type' => $lastMessage->message_type,
                        'sender' => [
                            'name' => $lastMessage->sender->name
                        ]
                    ] : null,
                    'unread_count' => $unreadCount,
                    'updated_at' => $complaint->updated_at
                ];
            }

            usort($chatRooms, function ($a, $b) {
                return strtotime($b['updated_at']) - strtotime($a['updated_at']);
            });

            return response()->json([
                'success' => true,
                'data' => $chatRooms
            ]);

        } catch (\Exception $e) {
            \Log::error('Chat rooms error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch chat rooms'
            ], 500);
        }
    }

    public function getMessages($complaint_id)
    {
        try {
            $complaint = Complaints::where('complaint_id', $complaint_id)->first();

            if (!$complaint) {
                return response()->json([
                    'success' => false,
                    'error' => 'Complaint not found'
                ], 404);
            }

            if (!$complaint->classification) {
                return response()->json([
                    'success' => false,
                    'error' => 'Chat not available for unclassified complaints'
                ], 403);
            }

            $messages = Chat::with('sender')
                ->where('complaint_id', $complaint_id)
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $messages
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch messages'
            ], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        $hasFile = $request->hasFile('file');

        $rules = [
            'complaint_id' => 'required|string',
            'sender_id' => 'required|string',
            'sender_type' => 'required|in:user,admin,psychologist',
            'message_text' => $hasFile ? 'nullable|string|max:1000' : 'required|string|max:1000',
            'message_type' => 'required|in:text,image,file',
        ];

        if ($hasFile) {
            $rules['file'] = 'required|file|max:5120|mimes:jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx,txt';
        } else {
            $rules['file_url'] = 'nullable|string';
            $rules['file_name'] = 'nullable|string';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first()
            ], 400);
        }

        try {
            $complaint = Complaints::where('complaint_id', $request->complaint_id)->first();

            if (!$complaint) {
                return response()->json([
                    'success' => false,
                    'error' => 'Complaint not found'
                ], 404);
            }

            if (!$complaint->classification) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot send message to unclassified complaint'
                ], 403);
            }

            $fileUrl = null;
            $fileName = null;

            if ($hasFile) {
                $file = $request->file('file');
                $filename = 'chat_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('chat/files', $filename, 'public');
                $fileUrl = Storage::disk('public')->url($path);
                $fileName = $file->getClientOriginalName();
            } else {
                $fileUrl = $request->file_url;
                $fileName = $request->file_name;
            }

            $message = Chat::create([
                'complaint_id' => $request->complaint_id,
                'sender_id' => $request->sender_id,
                'sender_type' => $request->sender_type,
                'message_text' => $request->message_text ?? '',
                'message_type' => $request->message_type,
                'file_url' => $fileUrl,
                'file_name' => $fileName,
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $message->load('sender');
            $complaint->touch();

            return response()->json([
                'success' => true,
                'data' => $message
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Failed to send message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'complaint_id' => 'required|string',
            'user_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first()
            ], 400);
        }

        try {
            $complaint = Complaints::where('complaint_id', $request->complaint_id)->first();

            if (!$complaint) {
                return response()->json([
                    'success' => false,
                    'error' => 'Complaint not found'
                ], 404);
            }

            $user = User::where('uuid', $request->user_id)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not found'
                ], 404);
            }

            $userType = 'user';
            if ($user->role === 'admin' || $user->role === 'superadmin') {
                $userType = 'admin';
            } elseif ($user->role === 'psychologist') {
                $userType = 'psychologist';
            }

            Chat::where('complaint_id', $request->complaint_id)
                ->where('sender_type', '!=', $userType)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark messages as read'
            ], 500);
        }
    }

    public function uploadFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:5120|mimes:jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx,txt'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first()
            ], 400);
        }

        try {
            $file = $request->file('file');
            $filename = 'chat_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('chat/files', $filename, 'public');
            $url = Storage::disk('public')->url($path);

            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $url,
                    'name' => $file->getClientOriginalName()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to upload file'
            ], 500);
        }
    }

    public function checkChatEnabled($complaint_id)
    {
        try {
            $complaint = Complaints::where('complaint_id', $complaint_id)->first();

            if (!$complaint) {
                return response()->json([
                    'success' => false,
                    'error' => 'Complaint not found'
                ], 404);
            }

            $enabled = !is_null($complaint->classification);

            return response()->json([
                'success' => true,
                'data' => [
                    'enabled' => $enabled
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to check chat status'
            ], 500);
        }
    }

    public function getUnreadCount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string',
            'user_type' => 'required|in:user,admin,psychologist'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first()
            ], 400);
        }

        try {
            $userId = $request->user_id;
            $userType = $request->user_type;

            $query = Chat::where('is_read', false);

            if ($userType === 'user') {
                $query->where('sender_type', '!=', 'user');
            } else {
                $query->where('sender_type', 'user');
            }

            if ($userType === 'user') {
                $query->whereHas('complaint', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
            }

            $count = $query->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get unread count'
            ], 500);
        }
    }
}


