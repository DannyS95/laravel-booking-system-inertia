<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Service;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\EmployeeResource;
use App\Feature\Service\ServiceAvailability;
use App\Http\Resources\AvailabilityResource;

class CheckoutController extends Controller
{
    public function __invoke(Service $service, Employee $employee, Request $request)
    {
        $employeeAvailability = (new ServiceAvailability($employee->exists ? collect([$employee]) : Employee::get(), $service))
            ->forPeriod(
                Carbon::createFromDate($request->calendar)->startOfDay(),
                Carbon::createFromDate($request->calendar)->endOfMonth(),
            );

        $availableDates = $employeeAvailability->hasSlots();

        return inertia()->render('Checkout', [
            'employee' => $employee->exists ? EmployeeResource::make($employee) : null,
            'employeeAvailability' => AvailabilityResource::collection($availableDates),
            'service' => ServiceResource::make($service),
            'start' => $employeeAvailability->firstAvailableDate()?->date->toDateString(),
            'calendar' => $request->calendar
        ]);
    }
}
