<?php

namespace App\Contracts\Repositories;

use App\Base\Contracts\Repositories\BaseRepository;
use App\Contracts\Interfaces\EvidenceInterface;
use App\Models\Evidence;

class EvidenceRepository extends BaseRepository implements EvidenceInterface
{
    public function __construct(Evidence $evidence)
    {
        $this->model = $evidence;
    }

    /**
     * @inheritDoc
     */
    public function store(array $data): bool|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|array
    {
        return $this->model->create($data);
    }

    /**
     * @inheritDoc
     */
    public function getByComplaintId(string $complaintId): array|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|bool
    {
        return $this->model->where('complaint_id', $complaintId)->get();
    }

    /**
     * @inheritDoc
     */
    public function multipleStore(array $data): bool|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|array
    {
        return $this->model->insert($data);
    }
}
