<?php

namespace App\Contracts\Repositories;

use App\Base\Contracts\Repositories\BaseRepository;
use App\Contracts\Interfaces\UserBundlePointInterface;
use App\Models\UserBundlePoint;
use Request;

class UserBundlePointRepository extends BaseRepository implements UserBundlePointInterface
{
    public function __construct(UserBundlePoint $userBundlePoint)
    {
        $this->model = $userBundlePoint;
    }
    /**
     * @inheritDoc
     */
    public function get(): array|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
    {
        return $this->model->all();
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
        $bundle = $this->show($uuid);
        return $bundle->update($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $uuid): bool|array|\Illuminate\Database\Eloquent\Collection
    {
        $bundle = $this->show($uuid);
        return $bundle->delete();
    }

    /**
     * @inheritDoc
     */
    public function show(string $uuid): \Illuminate\Database\Eloquent\Model|array|\Illuminate\Database\Eloquent\Collection
    {
        return $this->model->findOrFail($uuid);
    }

    public function getUserBundlePoints(string $userId): array|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
    {
        $data = $this->model->where("user_id", $userId)->get();
        return [
            "point_details" => $data,
            "available_points" => $data->sum('current_points')
        ];
    }
}
