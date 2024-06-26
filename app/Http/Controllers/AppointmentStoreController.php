<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Service;
use App\Models\Employee;
use App\Models\Appointment;
use App\Feature\Service\ServiceAvailability;
use App\Http\Requests\AppointmentStoreRequest;

class AppointmentStoreController extends Controller
{
    public function __invoke(AppointmentStoreRequest $request)
    {
        $employee = Employee::find($request->employee_id);
        $service = Service::find($request->service_id);

        $availability = (new ServiceAvailability(collect([$employee]), $service))
            ->forPeriod(
                Carbon::parse($request->datetime)->startOfDay(),
                Carbon::parse($request->datetime)->endOfDay(),
            );

        if (!$availability->first()->containsSlot(Carbon::parse($request->datetime)->toTimeString())) {
            return back()->with('message', 'That appointment was taken while you were in checkout. Please try another time.');
        }

        $appointment = Appointment::create($request->only(['employee_id', 'service_id', 'name', 'email']) + [
            'starts_at' => $date = Carbon::parse($request->datetime),
            'ends_at' => $date->copy()->addMinutes($service->duration),
        ]);

        return redirect()->route('appointments.show', $appointment);
    }
}
