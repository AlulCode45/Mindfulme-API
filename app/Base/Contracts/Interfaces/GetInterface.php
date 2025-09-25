<?php

namespace App\Base\Contracts\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface GetInterface
{
    public function get(): array|Model|Collection;
}
