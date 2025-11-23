<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\PsychologistAvailabilityInterface;
use App\Models\Appointments;
use App\Models\PsychologistAvailability;
use Carbon\Carbon;

class PsychologistAvailabilityRepository implements PsychologistAvailabilityInterface
{
    protected PsychologistAvailability $model;

    public function __construct(PsychologistAvailability $availability)
    {
        $this->model = $availability;
    }

    public function get(): array
    {
        return $this->model->with(['psychologist:uuid,name,email'])->get()->toArray();
    }

    public function getByPsychologistId(string $psychologistId): array
    {
        return $this->model->where('psychologist_id', $psychologistId)
            ->orderByRaw("CASE day_of_week
                WHEN 'monday' THEN 1
                WHEN 'tuesday' THEN 2
                WHEN 'wednesday' THEN 3
                WHEN 'thursday' THEN 4
                WHEN 'friday' THEN 5
                WHEN 'saturday' THEN 6
                WHEN 'sunday' THEN 7
                ELSE 8
            END")
            ->get()
            ->toArray();
    }

    public function getActiveAvailability(string $psychologistId): array
    {
        $today = Carbon::now()->toDateString();

        return $this->model->where('psychologist_id', $psychologistId)
            ->where('is_available', true)
            ->where(function ($query) use ($today) {
                $query->whereNull('effective_from')
                      ->orWhere('effective_from', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('effective_to')
                      ->orWhere('effective_to', '>=', $today);
            })
            ->orderByRaw("CASE day_of_week
                WHEN 'monday' THEN 1
                WHEN 'tuesday' THEN 2
                WHEN 'wednesday' THEN 3
                WHEN 'thursday' THEN 4
                WHEN 'friday' THEN 5
                WHEN 'saturday' THEN 6
                WHEN 'sunday' THEN 7
                ELSE 8
            END")
            ->get()
            ->toArray();
    }

    public function store(array $data): PsychologistAvailability
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): PsychologistAvailability
    {
        $availability = $this->model->findOrFail($id);
        $availability->update($data);
        return $availability;
    }

    public function delete(string $id): bool
    {
        $availability = $this->model->findOrFail($id);
        return $availability->delete();
    }

    public function show(string $id): PsychologistAvailability
    {
        return $this->model->with(['psychologist:uuid,name,email'])->findOrFail($id);
    }

    public function getAvailableTimeSlots(string $psychologistId, string $date, int $durationMinutes): array
    {
        $targetDate = Carbon::createFromFormat('Y-m-d', $date);
        $dayOfWeek = strtolower($targetDate->format('l'));

        // Get psychologist availability for the day
        $availabilities = $this->model->where('psychologist_id', $psychologistId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->where(function ($query) use ($targetDate) {
                $query->whereNull('effective_from')
                      ->orWhere('effective_from', '<=', $targetDate->toDateString());
            })
            ->where(function ($query) use ($targetDate) {
                $query->whereNull('effective_to')
                      ->orWhere('effective_to', '>=', $targetDate->toDateString());
            })
            ->get();

        // Get existing appointments for that day
        $existingAppointments = Appointments::where('psychologist_id', $psychologistId)
            ->where('start_time', '>=', $targetDate->startOfDay())
            ->where('start_time', '<', $targetDate->endOfDay())
            ->whereIn('status', ['scheduled', 'completed'])
            ->get();

        $availableSlots = [];

        foreach ($availabilities as $availability) {
            $startTime = Carbon::createFromFormat('Y-m-d H:i', $targetDate->format('Y-m-d') . ' ' . $availability->start_time);
            $endTime = Carbon::createFromFormat('Y-m-d H:i', $targetDate->format('Y-m-d') . ' ' . $availability->end_time);

            // Apply break periods if any
            $breakPeriods = $availability->break_periods ?? [];

            // Generate time slots
            $currentSlot = $startTime;
            while ($currentSlot->copy()->addMinutes($durationMinutes) <= $endTime) {
                $slotEnd = $currentSlot->copy()->addMinutes($durationMinutes);

                // Check if slot conflicts with break periods
                $conflictsWithBreak = false;
                foreach ($breakPeriods as $break) {
                    $breakStart = Carbon::createFromFormat('Y-m-d H:i', $targetDate->format('Y-m-d') . ' ' . $break['start']);
                    $breakEnd = Carbon::createFromFormat('Y-m-d H:i', $targetDate->format('Y-m-d') . ' ' . $break['end']);

                    if ($currentSlot < $breakEnd && $slotEnd > $breakStart) {
                        $conflictsWithBreak = true;
                        break;
                    }
                }

                // Check if slot conflicts with existing appointments
                $conflictsWithAppointment = false;
                foreach ($existingAppointments as $appointment) {
                    $appointmentStart = Carbon::parse($appointment->start_time);
                    $appointmentEnd = Carbon::parse($appointment->end_time);

                    if ($currentSlot < $appointmentEnd && $slotEnd > $appointmentStart) {
                        $conflictsWithAppointment = true;
                        break;
                    }
                }

                if (!$conflictsWithBreak && !$conflictsWithAppointment) {
                    $availableSlots[] = [
                        'start_time' => $currentSlot->format('H:i'),
                        'end_time' => $slotEnd->format('H:i'),
                        'available' => true
                    ];
                }

                $currentSlot->addMinutes(30); // Move to next 30-minute slot
            }
        }

        return $availableSlots;
    }

    public function checkTimeSlotAvailability(string $psychologistId, string $date, string $startTime, string $endTime): bool
    {
        $targetDate = Carbon::createFromFormat('Y-m-d', $date);
        $dayOfWeek = strtolower($targetDate->format('l'));
        $slotStart = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $startTime);
        $slotEnd = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $endTime);

        // Check if psychologist has availability for this day and time
        $availability = $this->model->where('psychologist_id', $psychologistId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->where('start_time', '<=', $startTime)
            ->where('end_time', '>=', $endTime)
            ->where(function ($query) use ($targetDate) {
                $query->whereNull('effective_from')
                      ->orWhere('effective_from', '<=', $targetDate->toDateString());
            })
            ->where(function ($query) use ($targetDate) {
                $query->whereNull('effective_to')
                      ->orWhere('effective_to', '>=', $targetDate->toDateString());
            })
            ->first();

        if (!$availability) {
            return false;
        }

        // Check if slot conflicts with break periods
        $breakPeriods = $availability->break_periods ?? [];
        foreach ($breakPeriods as $break) {
            $breakStart = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $break['start']);
            $breakEnd = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $break['end']);

            if ($slotStart < $breakEnd && $slotEnd > $breakStart) {
                return false;
            }
        }

        // Check if slot conflicts with existing appointments
        $conflictingAppointment = Appointments::where('psychologist_id', $psychologistId)
            ->where('start_time', '<', $slotEnd)
            ->where('end_time', '>', $slotStart)
            ->whereIn('status', ['scheduled', 'completed'])
            ->first();

        return !$conflictingAppointment;
    }
}