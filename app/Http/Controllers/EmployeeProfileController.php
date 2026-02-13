<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class EmployeeProfileController extends Controller
{
    public function update(EmployeeProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $employee = $user?->employee;

        if (!$employee) {
            return Redirect::route('profile.edit')->with('status', 'employee-not-found');
        }

        $employee->fill($request->validated());
        $employee->save();

        return Redirect::route('profile.edit')->with('status', 'employee-updated');
    }
}
