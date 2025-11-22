<?php

namespace App\Contracts\Interfaces;

use App\Models\SessionType;

interface SessionTypeInterface
{
    public function get(): array;

    public function getActive(): array;

    public function getByConsultationType(string $consultationType): array;

    public function store(array $data): SessionType;

    public function update(string $id, array $data): SessionType;

    public function delete(string $id): bool;

    public function show(string $id): SessionType;
}