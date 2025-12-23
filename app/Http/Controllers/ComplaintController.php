<?php

namespace App\Http\Controllers;

use App\Contracts\Interfaces\ComplaintInterface;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ComplaintController extends Controller
{
    private ComplaintInterface $complaint;

    public function __construct(ComplaintInterface $complaint)
    {
        $this->complaint = $complaint;
    }

    public function index()
    {
        return ResponseHelper::success($this->complaint->get(), 'Complaints retrieved successfully');
    }

    public function getComplaintByUserUuid(Request $request, string $uuid)
    {
        try {
            $withSessions = $request->query('with_sessions', false);
            $complaints = $this->complaint->getByUserUuid($uuid, $withSessions);
            return ResponseHelper::success($complaints, 'User complaints retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }

    public function show(string $uuid)
    {
        try {
            return ResponseHelper::success($this->complaint->show($uuid), 'Complaint retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }

    public function storeComplaint(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'title' => 'required|string|max:200',
                'chronology' => 'required|string|max:2000',
                'category' => 'required|string',
                'description' => 'nullable|string|max:1000',
                'chat_history' => 'nullable|string',
                'evidence' => 'nullable|array',
                'evidence.*' => 'file|mimes:jpeg,jpg,png,pdf,doc,docx|max:10240' // Max 10MB per file
            ]);

            $evidenceFiles = [];

            // Handle file uploads
            if ($request->hasFile('evidence')) {
                foreach ($request->file('evidence') as $file) {
                    // Generate unique filename
                    $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

                    // Store file in storage/app/public/complaints/evidence
                    $path = $file->storeAs('complaints/evidence', $filename, 'public');

                    $evidenceFiles[] = [
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'url' => asset('storage/' . $path),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize()
                    ];
                }
            }

            $data = [
                'user_id' => auth()->user()->uuid,
                'title' => $validated['title'],
                'chronology' => $validated['chronology'],
                'category' => $validated['category'],
                'description' => $validated['description'] ?? null,
                'chat_history' => $validated['chat_history'] ?? null,
                'evidence_files' => !empty($evidenceFiles) ? json_encode($evidenceFiles) : null,
                'status' => 'new',
                'classification' => null
            ];

            $complaint = $this->complaint->store($data);

            return ResponseHelper::success($complaint, 'Complaint submitted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }

    public function destroy(string $uuid)
    {
        try {
            // Get complaint first to delete associated files
            $complaint = $this->complaint->show($uuid);

            // Delete evidence files from storage if they exist
            if ($complaint->evidence_files) {
                $evidenceFiles = is_string($complaint->evidence_files)
                    ? json_decode($complaint->evidence_files, true)
                    : $complaint->evidence_files;

                foreach ($evidenceFiles as $file) {
                    if (isset($file['path']) && Storage::disk('public')->exists($file['path'])) {
                        Storage::disk('public')->delete($file['path']);
                    }
                }
            }

            $this->complaint->delete($uuid);
            return ResponseHelper::success([], 'Complaint deleted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }

    /**
     * Get complaints for psychologist (filtered by psychology classification)
     */
    public function getPsychologistComplaints()
    {
        try {
            // Get all complaints classified as psychology
            $complaints = $this->complaint->getPsychologistComplaints();
            return ResponseHelper::success($complaints, 'Psychologist complaints retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }

    /**
     * Update complaint status and details
     */
    public function update(Request $request, string $uuid)
    {
        try {
            $data = $request->validate([
                'status' => 'sometimes|in:new,in-progress,completed,urgent',
                'assigned_to' => 'sometimes|string|max:255',
                'admin_notes' => 'sometimes|string|max:1000',
                'priority' => 'sometimes|in:low,medium,high,urgent',
                'classification' => 'sometimes|in:psikologi,hukum',
                'scheduled_date' => 'sometimes|date',
                'scheduled_time' => 'sometimes|string'
            ]);

            $complaint = $this->complaint->show($uuid);
            $updatedComplaint = $this->complaint->update($data, $uuid);

            return ResponseHelper::success($updatedComplaint, 'Complaint updated successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }

    /**
     * Send response to complaint
     */
    public function sendResponse(Request $request, string $uuid)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:2000',
                'admin_id' => 'sometimes|string'
            ]);

            $complaint = $this->complaint->show($uuid);

            // Create response record (you might need to create a ComplaintResponse model)
            // For now, we'll just update the admin notes
            $data = [
                'admin_notes' => ($complaint->admin_notes ?? '') . "\n\nResponse: " . $request->message,
                'status' => 'in-progress'
            ];

            $updatedComplaint = $this->complaint->update($data, $uuid);

            return ResponseHelper::success($updatedComplaint, 'Response sent successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }

    /**
     * Get complaint responses/history
     */
    public function getResponses(string $uuid)
    {
        try {
            $complaint = $this->complaint->show($uuid);

            // For now, return basic info. You might want to create a proper response history
            $responses = [
                [
                    'id' => 'system-001',
                    'type' => 'system',
                    'author' => 'Sistem',
                    'message' => 'Pengaduan telah dibuat',
                    'timestamp' => $complaint->created_at
                ]
            ];

            if ($complaint->admin_notes) {
                $responses[] = [
                    'id' => 'admin-001',
                    'type' => 'admin',
                    'author' => 'Admin',
                    'message' => $complaint->admin_notes,
                    'timestamp' => $complaint->updated_at
                ];
            }

            return ResponseHelper::success($responses, 'Complaint responses retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }

    /**
     * Get complaint sessions/consultations
     */
    public function getSessions(string $uuid)
    {
        try {
            $complaint = $this->complaint->show($uuid);
            $sessions = $complaint->sessions ?? [];

            return ResponseHelper::success($sessions, 'Complaint sessions retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }

    /**
     * Classify complaint
     */
    public function classify(Request $request, string $uuid)
    {
        try {
            $data = $request->validate([
                'classification' => 'required|in:psikologi,hukum',
                'admin_notes' => 'required|string|max:1000',
                'scheduled_date' => 'sometimes|date',
                'scheduled_time' => 'sometimes|string',
                'assigned_to' => 'sometimes|string|max:255',
                'meeting_link' => 'sometimes|string|max:500',
                'notification_message' => 'sometimes|string'
            ]);

            $complaint = $this->complaint->show($uuid);

            // Set status to in-progress when classified
            $data['status'] = 'in-progress';

            $updatedComplaint = $this->complaint->update($data, $uuid);

            return ResponseHelper::success($updatedComplaint, 'Complaint classified successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }

    /**
     * Get evidence files for a complaint
     */
    public function getEvidence(string $uuid)
    {
        try {
            $complaint = $this->complaint->show($uuid);

            if (!$complaint) {
                return ResponseHelper::error('Complaint not found', 404);
            }

            $evidenceFiles = [];
            if ($complaint->evidence_files) {
                $evidenceFiles = is_string($complaint->evidence_files)
                    ? json_decode($complaint->evidence_files, true)
                    : $complaint->evidence_files;
            }

            return ResponseHelper::success($evidenceFiles ?? [], 'Evidence files retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }
}

