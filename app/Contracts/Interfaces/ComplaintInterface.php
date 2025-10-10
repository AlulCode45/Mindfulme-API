<?php

namespace App\Contracts\Interfaces;

use App\Base\Contracts\Interfaces\DeleteInterface;
use App\Base\Contracts\Interfaces\GetInterface;
use App\Base\Contracts\Interfaces\ShowInterface;
use App\Base\Contracts\Interfaces\StoreInterface;
use App\Base\Contracts\Interfaces\UpdateInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ComplaintInterface extends GetInterface, StoreInterface, UpdateInterface, DeleteInterface, ShowInterface
{
    public function getByUserUuid(string $uuid): array|Collection|Model;
}
