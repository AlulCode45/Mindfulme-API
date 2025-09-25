<?php

namespace App\Base\Contracts\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface DeleteInterface
{
    public function delete(): bool|array|Collection;
}
