<?php

namespace App\Feature\Service;

use Carbon\Carbon;
use App\Models\Service;
use App\Models\Employee;
use Spatie\Period\Period;
use App\Models\Appointment;
use Spatie\Period\Precision;
use App\Feature\Booking\Slot;
use Spatie\Period\Boundaries;
use App\Feature\Booking\Slots;
use Illuminate\Support\Collection;
use Spatie\Period\PeriodCollection;
use App\Feature\Booking\SlotRangeGenerator;
use App\Feature\Collections\SlotCollection;
use App\Feature\Employee\ScheduleAvailability;
/**
 * Core class for building out employee schedule availability
 */
final class ServiceAvailability
{
    public function __construct(private Collection $employees, private Service $service)
    {
        //
    }

    public function forPeriod(Carbon $startsAt, Carbon $endsAt): SlotCollection
    {
        /**
         * @var SlotCollection $slotRange
         */
        $slotRange = (new SlotRangeGenerator($startsAt, $endsAt))->generate(slotRange: $this->service->duration);

        $this->employees->each(function (Employee $employee) use ($startsAt, $endsAt, &$slotRange) {
            $schedule = (new ScheduleAvailability($employee, $this->service))
                ->forPeriod($startsAt, $endsAt);

            $schedule = $this->removeAppointments($schedule, $employee);

            foreach ($schedule as $availability) {
                $this->addScheduleAvailabilityToSlots($slotRange, $availability, $employee);
            }
        });

        $slotRange = $this->removeEmptySlots($slotRange);

        return $slotRange;
    }

    private function removeAppointments(PeriodCollection $schedule, Employee $employee): PeriodCollection
    {
        $employee->appointments->whereNull('cancelled_at')->each(function (Appointment $appointment) use (&$schedule) {
            $schedule = $schedule->subtract(
                Period::make(
                    $appointment->starts_at->copy()->subMinutes($this->service->duration)->addMinute(),
                    $appointment->ends_at,
                    Precision::MINUTE(),
                    Boundaries::EXCLUDE_ALL()
                )
            );
        });

        return $schedule;
    }

    private function removeEmptySlots(SlotCollection $range): SlotCollection
    {
        return $range->filter(function (Slots $slots) {
            $slots->slots = $slots->slots->filter(function (Slot $slot) {
                return $slot->hasEmployees();
            });

            return true;
        });
    }

    /**
     * Function to attach employees to the range slots
     *
     * @param SlotCollection $slotRange The range to which we want to add employee availability
     * @param Period $availability A period from an Employee's schedule
     * @param Employee $employee
     * @return void
     */
    private function addScheduleAvailabilityToSlots(SlotCollection $slotRange, Period $availability, Employee $employee): void
    {
        $slotRange->each(function (Slots $slots) use ($availability, $employee) {
            $slots->slots->each(function (Slot $slot) use ($availability, $employee) {
                # we are checking if the period through which an employee works fits in the slot
                if ($availability->contains($slot->time)) {
                    $slot->addEmployee($employee);
                }
            });
        });
    }
}
