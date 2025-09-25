<?php

namespace App\Base\Contracts\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface StoreInterface
{
    public function store(array $data): bool|Model|Collection|array;
}
