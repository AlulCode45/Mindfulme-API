<?php

namespace App\Base\Contracts\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;

interface GetInterface
{
    public function get(): array|Model|Collection|SupportCollection;
}
