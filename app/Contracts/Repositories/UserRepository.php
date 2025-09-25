<?php

namespace App\Contracts\Repositories;

use App\Base\Contracts\Repositories\BaseRepository;
use App\Contracts\Interfaces\UserInterface;
use App\Models\User;

class UserRepository extends BaseRepository implements UserInterface
{
    public function __construct(User $user)
    {
        $this->model = $user;
    }


    /**
     * @inheritDoc
     */
    public function get(): array|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
    {
    }

    /**
     * @inheritDoc
     */
    public function store(array $data): bool|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|array
    {
    }

    /**
     * @inheritDoc
     */
    public function update(array $data): bool|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|array
    {
    }

    /**
     * @inheritDoc
     */
    public function delete(): bool|array|\Illuminate\Database\Eloquent\Collection
    {
    }
}
