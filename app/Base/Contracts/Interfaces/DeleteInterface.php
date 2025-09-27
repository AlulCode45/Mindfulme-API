<?php

namespace App\Base\Contracts\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface DeleteInterface
{
    public function delete(string $uuid): bool|array|Collection|Model;
}
