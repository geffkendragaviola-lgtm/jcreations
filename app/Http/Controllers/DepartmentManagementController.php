<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class DepartmentManagementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $departments = Department::query()
            ->orderBy('name')
            ->get();

        return view('departments.index', [
            'departments' => $departments,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'business_hours_start' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'business_hours_end' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
        ]);

        $start = $validated['business_hours_start'] ?? null;
        $end = $validated['business_hours_end'] ?? null;

        $start = is_string($start) ? substr(trim($start), 0, 5) : null;
        $end = is_string($end) ? substr(trim($end), 0, 5) : null;

        if ($start === '') {
            $start = null;
        }
        if ($end === '') {
            $end = null;
        }

        $nextId = ((int) Department::query()->max('id')) + 1;

        Department::query()->create([
            'id' => $nextId,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'business_hours_start' => $start,
            'business_hours_end' => $end,
        ]);

        return Redirect::route('departments.index')->with('status', 'department-created');
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'business_hours_start' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'business_hours_end' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $start = $validated['business_hours_start'] ?? null;
        $end = $validated['business_hours_end'] ?? null;

        $start = is_string($start) ? substr(trim($start), 0, 5) : null;
        $end = is_string($end) ? substr(trim($end), 0, 5) : null;

        if ($start === '') {
            $start = null;
        }
        if ($end === '') {
            $end = null;
        }

        $department->name = $validated['name'];
        $department->description = $validated['description'] ?? null;
        $department->business_hours_start = $start;
        $department->business_hours_end = $end;
        $department->save();

        $redirectTo = $request->input('redirect_to');
        if (is_string($redirectTo) && $redirectTo !== '') {
            if (str_starts_with($redirectTo, '/')) {
                return redirect($redirectTo)->with('status', 'department-updated');
            }

            $appUrl = rtrim((string) config('app.url'), '/');
            if ($appUrl !== '' && str_starts_with($redirectTo, $appUrl)) {
                return redirect()->to($redirectTo)->with('status', 'department-updated');
            }
        }

        return Redirect::route('departments.index')->with('status', 'department-updated');
    }
}
