<?php

namespace App\Http\Controllers;

use App\Contracts\Interfaces\AiDiscussionInterface;
use App\Contracts\Interfaces\ComplaintInterface;
use App\Contracts\Interfaces\EvidenceInterface;
use App\Enums\ComplaintStatus;
use App\Helpers\ResponseHelper;
use App\Http\Requests\SaveConversationRequest;
use App\Http\Requests\SendConversationToComplaintRequest;
use App\Traits\UploadFile;
use Illuminate\Http\Request;

class AiDiscussionController extends Controller
{
    use UploadFile;
    private AiDiscussionInterface $aiDiscussion;
    private ComplaintInterface $complaint;
    private EvidenceInterface $evidence;
    public function __construct(AiDiscussionInterface $aiDiscussion, ComplaintInterface $complaint, EvidenceInterface $evidence)
    {
        $this->aiDiscussion = $aiDiscussion;
        $this->complaint = $complaint;
        $this->evidence = $evidence;
    }

    public function get()
    {
        return ResponseHelper::success($this->aiDiscussion->get(), 'Conversations retrieved successfully');
    }

    public function saveConversation(SaveConversationRequest $request)
    {
        // When Request Have ai_discussion_id, Update the Conversation
        if ($request->ai_discussion_id) {
            try {
                $data = [
                    'conversation' => json_encode($request->conversation),
                    'identified_issue' => $request->identified_issue,
                    'summary' => $request->summary,
                ];
                $this->aiDiscussion->update($data, $request->ai_discussion_id);
                return ResponseHelper::success($data, 'Conversation updated successfully');
            } catch (\Exception $e) {
                return ResponseHelper::error($e->getMessage(), 500);
            }
        }

        // When Request Don't Have ai_discussion_id, Create New Conversation
        try {
            $data = [
                'user_id' => auth()->user()->uuid,
                'conversation' => json_encode($request->conversation),
                'identified_issue' => $request->identified_issue,
                'summary' => $request->summary,
            ];
            $this->aiDiscussion->store($data);
            return ResponseHelper::success($data, 'Conversation saved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
    public function getConversationsByUserUuid(string $uuid)
    {
        try {
            return ResponseHelper::success($this->aiDiscussion->getByUserUuid($uuid), 'Conversations retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
    public function getConversationsByUuid(string $uuid)
    {
        try {
            $conversation = $this->aiDiscussion->show($uuid);
            return ResponseHelper::success($conversation, 'Conversation retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
    public function deleteConversation(string $uuid)
    {
        try {
            $this->aiDiscussion->delete($uuid);
            return ResponseHelper::success(null, 'Conversation deleted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
    public function sendConversationToComplaint(string $uuid, SendConversationToComplaintRequest $request)
    {
        $conversation = $this->aiDiscussion->show($uuid);
        if (!$conversation) {
            return ResponseHelper::error('Conversation not found', 404);
        }

        $data = [
            'user_id' => $conversation->user_id,
            'title' => $conversation->identified_issue ?? 'No title',
            'description' => $conversation->summary ?? 'No description',
            'chronology' => $conversation->conversation,
            'category' => 'General', // TODO: Update category based on your logic
            'status' => ComplaintStatus::NEW ,
        ];
        try {
            $complaint = $this->complaint->store($data);
            // Implementation Evidence handling
            if ($request->hasFile('evidence')) {
                $files = $request->file('evidence');
                $evidenceData = [];
                foreach ($files as $file) {
                    $filePath = $this->uploadFile($file, directory: 'complaint_evidences');
                    $evidenceData[] = [
                        'evidence_id' => (string) \Illuminate\Support\Str::uuid(),
                        'complaint_id' => $complaint->complaint_id,
                        'file_path' => $filePath,
                        'file_type' => $file->getClientMimeType(),
                    ];
                }
                $this->evidence->multipleStore($evidenceData);
            }
            return ResponseHelper::success([
                ...$data,
                'evidences' => $evidenceData ?? []
            ], 'Complaint created successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
