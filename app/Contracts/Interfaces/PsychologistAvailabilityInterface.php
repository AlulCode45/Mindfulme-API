<?php

namespace App\Contracts\Interfaces;

use App\Models\PsychologistAvailability;

interface PsychologistAvailabilityInterface
{
    public function get();

    public function getByPsychologistId(string $psychologistId): array;

    public function getActiveAvailability(string $psychologistId): array;

    public function store(array $data): PsychologistAvailability;

    public function update(string $id, array $data): PsychologistAvailability;

    public function delete(string $id): bool;

    public function show(string $id): PsychologistAvailability;

    public function getAvailableTimeSlots(string $psychologistId, string $date, int $durationMinutes): array;

    public function checkTimeSlotAvailability(string $psychologistId, string $date, string $startTime, string $endTime): bool;
}