<?php

namespace App\Contracts\Interfaces;

use App\Base\Contracts\Interfaces\DeleteInterface;
use App\Base\Contracts\Interfaces\GetInterface;
use App\Base\Contracts\Interfaces\ShowInterface;
use App\Base\Contracts\Interfaces\StoreInterface;
use App\Base\Contracts\Interfaces\UpdateInterface;

interface UserBundlePointInterface extends GetInterface, ShowInterface, UpdateInterface, DeleteInterface, StoreInterface
{
    public function getUserBundlePoints(string $userId): array|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection;
}
