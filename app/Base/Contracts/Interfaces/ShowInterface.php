<?php

namespace App\Base\Contracts\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ShowInterface
{
    public function show(string $uuid): Model|array|Collection;
}
