<?php

namespace App\Contracts\Interfaces;

use App\Base\Contracts\Interfaces\DeleteInterface;
use App\Base\Contracts\Interfaces\GetInterface;
use App\Base\Contracts\Interfaces\ShowInterface;
use App\Base\Contracts\Interfaces\StoreInterface;
use App\Base\Contracts\Interfaces\UpdateInterface;

interface EvidenceInterface extends StoreInterface
{
    public function getByComplaintId(string $complaintId): array|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|bool;

    public function multipleStore(array $data): bool|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|array;
}
