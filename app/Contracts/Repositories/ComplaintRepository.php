<?php

namespace App\Contracts\Repositories;

use App\Base\Contracts\Repositories\BaseRepository;
use App\Contracts\Interfaces\ComplaintInterface;
use App\Models\Complaints;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ComplaintRepository extends BaseRepository implements ComplaintInterface
{
    public function __construct(Complaints $model)
    {
        $this->model = $model;
    }
    /**
     * @inheritDoc
     */
    public function get(): array|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
    {
        return $this->model
            ->query()
            ->with('evidence', 'user.detail')
            ->orderBy('created_at', 'desc')
            ->get();
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
    public function update(array $data, string $uuid): bool|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|array
    {
        return $this->show($uuid)->update($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $uuid): bool|array|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
    {
        return $this->show($uuid)->delete();
    }

    /**
     * @inheritDoc
     */
    public function show(string $uuid): \Illuminate\Database\Eloquent\Model|array|\Illuminate\Database\Eloquent\Collection
    {
        return $this->model
            ->query()
            ->with('evidence', 'user.detail')
            ->findOrFail($uuid);
    }

    public function getByUserUuid(string $uuid, bool $withSessions = false): array|Collection|Model
    {
        $query = $this->model->where('user_id', $uuid)->orderBy('created_at', 'desc');

        if ($withSessions) {
            $query->with('sessions');
        }

        return $query->get();
    }

    /**
     * Get psychologist complaints (classified as psychology)
     */
    public function getPsychologistComplaints(): array|Collection
    {
        return $this->model
            ->where('classification', 'psikologi')
            ->with(['user.detail', 'evidence'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
