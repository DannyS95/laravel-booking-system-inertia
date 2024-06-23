<?php

namespace App\Http\Controllers;

use App\Http\Resources\EmployeeResource;
use App\Http\Resources\ServiceResource;
use App\Models\Employee;

class EmployeeServiceIndexController extends Controller
{
    public function __invoke(Employee $employee)
    {
        return inertia()->render('Employee', [
            'employee' => EmployeeResource::make($employee),
            'services' => ServiceResource::collection($employee->services)
        ]);
    }
}
