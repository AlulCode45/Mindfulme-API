<?php

namespace App\Base\Contracts\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface UpdateInterface
{
    public function update(array $data, string $uuid): bool|Model|Collection|array;
}
