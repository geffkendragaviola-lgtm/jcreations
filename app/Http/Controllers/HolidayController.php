<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $year = (int) $request->query('year', now()->year);

        $holidays = Holiday::query()
            ->whereYear('date', $year)
            ->orderBy('date')
            ->get();

        return view('holidays.index', [
            'holidays' => $holidays,
            'year' => $year,
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
            'date' => ['required', 'date'],
            'type' => ['required', 'string', 'in:regular,special_non_working,special_working'],
            'pay_multiplier' => ['required', 'numeric', 'min:0.5', 'max:5'],
            'description' => ['nullable', 'string'],
            'recurring' => ['nullable', 'boolean'],
        ]);

        $nextId = ((int) Holiday::query()->max('id')) + 1;

        Holiday::create([
            'id' => $nextId,
            'name' => $validated['name'],
            'date' => $validated['date'],
            'type' => $validated['type'],
            'pay_multiplier' => $validated['pay_multiplier'],
            'description' => $validated['description'] ?? null,
            'recurring' => $validated['recurring'] ?? false,
        ]);

        ActivityLogger::log('created', 'Holiday', $nextId, "Created holiday {$validated['name']}");

        return Redirect::route('holidays.index')->with('status', 'holiday-created');
    }

    public function update(Request $request, Holiday $holiday): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'type' => ['required', 'string', 'in:regular,special_non_working,special_working'],
            'pay_multiplier' => ['required', 'numeric', 'min:0.5', 'max:5'],
            'description' => ['nullable', 'string'],
            'recurring' => ['nullable', 'boolean'],
        ]);

        $holiday->update([
            'name' => $validated['name'],
            'date' => $validated['date'],
            'type' => $validated['type'],
            'pay_multiplier' => $validated['pay_multiplier'],
            'description' => $validated['description'] ?? null,
            'recurring' => $validated['recurring'] ?? false,
        ]);

        ActivityLogger::log('updated', 'Holiday', $holiday->id, "Updated holiday {$holiday->name}");

        return Redirect::route('holidays.index')->with('status', 'holiday-updated');
    }

    public function destroy(Request $request, Holiday $holiday): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $name = $holiday->name;
        $holiday->delete();

        ActivityLogger::log('deleted', 'Holiday', null, "Deleted holiday {$name}");

        return Redirect::route('holidays.index')->with('status', 'holiday-deleted');
    }
}
