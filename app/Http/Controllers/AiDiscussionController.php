<?php

namespace App\Http\Controllers;

use App\Contracts\Interfaces\AiDiscussionInterface;
use App\Contracts\Interfaces\ComplaintInterface;
use App\Enums\ComplaintStatus;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class AiDiscussionController extends Controller
{
    private AiDiscussionInterface $aiDiscussion;
    private ComplaintInterface $complaint;
    public function __construct(AiDiscussionInterface $aiDiscussion, ComplaintInterface $complaint)
    {
        $this->aiDiscussion = $aiDiscussion;
        $this->complaint = $complaint;
    }

    public function get()
    {
        return ResponseHelper::success($this->aiDiscussion->get(), 'Conversations retrieved successfully');
    }

    public function saveConversation(Request $request)
    {
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
    public function sendConversationToComplaint(string $uuid)
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
            $this->complaint->store($data);
            return ResponseHelper::success($data, 'Complaint created successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
