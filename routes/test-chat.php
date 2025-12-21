<?php

// Simple test endpoint for chat
Route::get('/test-chat', function() {
    return response()->json([
        'message' => 'Chat API is working!',
        'status' => 'success'
    ]);
});

Route::get('/test-chat-db', function() {
    try {
        // Check if chats table exists
        $hasChatsTable = Schema::hasTable('chats');

        // Check if complaints table exists
        $hasComplaintsTable = Schema::hasTable('complaints');

        return response()->json([
            'has_chats_table' => $hasChatsTable,
            'has_complaints_table' => $hasComplaintsTable,
            'chats_table_columns' => $hasChatsTable ? Schema::getColumnListing('chats') : null,
            'complaints_table_columns' => $hasComplaintsTable ? Schema::getColumnListing('complaints') : null,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Test specific complaint without auth
Route::get('/test-complaint/{uuid}', function($uuid) {
    try {
        $complaint = \App\Models\Complaints::where('complaint_id', $uuid)->first();

        if (!$complaint) {
            // List all complaints for debugging
            $allComplaints = \App\Models\Complaints::select('complaint_id', 'title', 'classification')->get();
            return response()->json([
                'success' => false,
                'error' => 'Complaint not found',
                'searching_for' => $uuid,
                'all_complaints' => $allComplaints
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'complaint_id' => $complaint->complaint_id,
                'title' => $complaint->title,
                'classification' => $complaint->classification,
                'has_classification' => !empty($complaint->classification)
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Test chat messages without auth
Route::get('/test-chat-messages/{complaint_id}', function($complaint_id) {
    try {
        $complaint = \App\Models\Complaints::where('complaint_id', $complaint_id)->first();

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

        $messages = \App\Models\Chat::with('sender')
            ->where('complaint_id', $complaint_id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages,
            'complaint_info' => [
                'complaint_id' => $complaint->complaint_id,
                'title' => $complaint->title,
                'classification' => $complaint->classification
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Debug auth and complaint lookup
Route::post('/debug-read', function(Illuminate\Http\Request $request) {
    try {
        // Get auth user
        $user = auth()->user();

        // Get request data
        $complaintId = $request->input('complaint_id');
        $userId = $request->input('user_id');

        // Try to find complaint
        $complaint = \App\Models\Complaints::where('complaint_id', $complaintId)->first();

        // Try to find user
        $userFromDb = \App\Models\User::where('uuid', $userId)->first();

        return response()->json([
            'authenticated_user' => $user ? ['uuid' => $user->uuid, 'email' => $user->email] : null,
            'request_data' => [
                'complaint_id' => $complaintId,
                'user_id' => $userId
            ],
            'complaint_found' => $complaint ? true : false,
            'user_from_db_found' => $userFromDb ? true : false,
            'all_complaints_ids' => \Illuminate\Support\Facades\DB::table('complaints')->pluck('complaint_id')->toArray()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
});

// Add sample data
Route::get('/add-sample-data', function() {
    try {
        // Get or create test users
        $user = \Illuminate\Support\Facades\DB::table('users')->where('email', 'testuser@example.com')->first();
        if (!$user) {
            $userId = (string)\Illuminate\Support\Str::uuid();
            \Illuminate\Support\Facades\DB::table('users')->insert([
                'uuid' => $userId,
                'name' => 'Test User',
                'email' => 'testuser@example.com',
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $userId = $user->uuid;
        }

        $admin = \Illuminate\Support\Facades\DB::table('users')->where('email', 'admin@example.com')->first();
        if (!$admin) {
            $adminId = (string)\Illuminate\Support\Str::uuid();
            \Illuminate\Support\Facades\DB::table('users')->insert([
                'uuid' => $adminId,
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $adminId = $admin->uuid;
        }

        // Update existing complaint (without status)
        \Illuminate\Support\Facades\DB::table('complaints')
            ->where('complaint_id', '04f8ffc3-5da1-4774-8ec1-25ae9478dcf2')
            ->update([
                'user_id' => $userId,
                'title' => 'Keluhan tentang kecemasan sosial',
                'description' => 'Saya merasa cemas berinteraksi dengan orang baru',
                'chronology' => 'Mulai terasa 3 bulan terakhir',
                'category' => 'mental',
                'classification' => 'psikologi',
            ]);

        // Add sample messages
        $messages = [
            [
                'complaint_id' => '04f8ffc3-5da1-4774-8ec1-25ae9478dcf2',
                'sender_id' => $userId,
                'sender_type' => 'user',
                'message_text' => 'Halo admin, saya ingin berbicara tentang masalah kecemasan saya',
                'message_type' => 'text',
                'is_read' => true,
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(30),
            ],
            [
                'complaint_id' => '04f8ffc3-5da1-4774-8ec1-25ae9478dcf2',
                'sender_id' => $adminId,
                'sender_type' => 'admin',
                'message_text' => 'Halo! Terima kasih telah menghubungi kami. Saya siap membantu Anda. Bisa ceritakan lebih detail tentang kecemasan yang Anda alami?',
                'message_type' => 'text',
                'is_read' => true,
                'created_at' => now()->subMinutes(25),
                'updated_at' => now()->subMinutes(25),
            ],
            [
                'complaint_id' => '04f8ffc3-5da1-4774-8ec1-25ae9478dcf2',
                'sender_id' => $userId,
                'sender_type' => 'user',
                'message_text' => 'Saya merasa nervous ketika harus presentasi di depan banyak orang',
                'message_type' => 'text',
                'is_read' => false,
                'created_at' => now()->subMinutes(20),
                'updated_at' => now()->subMinutes(20),
            ],
        ];

        // Clear existing messages for this complaint
        \Illuminate\Support\Facades\DB::table('chats')
            ->where('complaint_id', '04f8ffc3-5da1-4774-8ec1-25ae9478dcf2')
            ->delete();

        foreach ($messages as $message) {
            \Illuminate\Support\Facades\DB::table('chats')->insert($message);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sample data added successfully!',
            'complaint_id' => '04f8ffc3-5da1-4774-8ec1-25ae9478dcf2'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});