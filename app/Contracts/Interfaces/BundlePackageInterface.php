<?php

namespace App\Contracts\Interfaces;

use App\Base\Contracts\Interfaces\DeleteInterface;
use App\Base\Contracts\Interfaces\GetInterface;
use App\Base\Contracts\Interfaces\ShowInterface;
use App\Base\Contracts\Interfaces\StoreInterface;
use App\Base\Contracts\Interfaces\UpdateInterface;

interface BundlePackageInterface extends GetInterface, StoreInterface, UpdateInterface, DeleteInterface, ShowInterface
{

}
