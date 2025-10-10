<?php

namespace App\Http\Controllers;

use App\Contracts\Interfaces\ComplaintInterface;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

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

    public function getComplaintByUserUuid(string $uuid)
    {
        try {
            return ResponseHelper::success($this->complaint->getByUserUuid($uuid), 'User complaints retrieved successfully');
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
            $data = [
                'user_id' => auth()->user()->uuid,
                'title' => $request->title,
                'chronology' => $request->chronology,
                'category' => $request->category,
            ];
            $this->complaint->store($data);
            return ResponseHelper::success($data, 'Complaint submitted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }
    public function destroy(string $uuid)
    {
        try {
            $this->complaint->delete($uuid);
            return ResponseHelper::success([], 'Complaint deleted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }
}
