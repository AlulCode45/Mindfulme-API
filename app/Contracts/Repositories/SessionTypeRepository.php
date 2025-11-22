<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\SessionTypeInterface;
use App\Models\SessionType;

class SessionTypeRepository implements SessionTypeInterface
{
    protected SessionType $model;

    public function __construct(SessionType $sessionType)
    {
        $this->model = $sessionType;
    }

    public function get(): array
    {
        return $this->model->get()->toArray();
    }

    public function getActive(): array
    {
        return $this->model->active()->get()->toArray();
    }

    public function getByConsultationType(string $consultationType): array
    {
        return $this->model->active()
            ->byConsultationType($consultationType)
            ->get()
            ->toArray();
    }

    public function store(array $data): SessionType
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): SessionType
    {
        $sessionType = $this->model->findOrFail($id);
        $sessionType->update($data);
        return $sessionType;
    }

    public function delete(string $id): bool
    {
        $sessionType = $this->model->findOrFail($id);
        return $sessionType->delete();
    }

    public function show(string $id): SessionType
    {
        return $this->model->findOrFail($id);
    }
}