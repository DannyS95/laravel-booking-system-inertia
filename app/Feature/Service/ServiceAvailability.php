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
         * @var SlotCollection $slotCollection
         */
        $range = (new SlotRangeGenerator($startsAt, $endsAt))->generate($this->service->duration);

        $this->employees->each(function (Employee $employee) use ($startsAt, $endsAt, &$range) {
            $periods = (new ScheduleAvailability($employee, $this->service))
                ->forPeriod($startsAt, $endsAt);

            $periods = $this->removeAppointments($periods, $employee);

            foreach ($periods as $period) {
                $this->addAvailableEmployeeForPeriod($range, $period, $employee);
            }
        });

        $range = $this->removeEmptySlots($range);

        return $range;
    }

    private function removeAppointments(PeriodCollection $period, Employee $employee)
    {
        $employee->appointments->whereNull('cancelled_at')->each(function (Appointment $appointment) use (&$period) {
            $period = $period->subtract(
                Period::make(
                    $appointment->starts_at->copy()->subMinutes($this->service->duration)->addMinute(),
                    $appointment->ends_at,
                    Precision::MINUTE(),
                    Boundaries::EXCLUDE_ALL()
                )
            );
        });

        return $period;
    }

    private function removeEmptySlots(Collection $range)
    {
        return $range->filter(function (Slots $date) {
            $date->slots = $date->slots->filter(function (Slot $slot) {
                return $slot->hasEmployees();
            });

            return true;
        });
    }

    private function addAvailableEmployeeForPeriod(Collection $range, Period $period, Employee $employee)
    {
        $range->each(function (Slots $date) use ($period, $employee) {
            $date->slots->each(function (Slot $slot) use ($period, $employee) {
                if ($period->contains($slot->time)) {
                    $slot->addEmployee($employee);
                }
            });
        });
    }
}
