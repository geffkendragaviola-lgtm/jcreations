<?php

namespace App\Http\Controllers;

use App\Models\DisciplinaryAction;
use App\Models\Employee;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class DisciplinaryActionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));

        $actions = DisciplinaryAction::query()
            ->with(['employee.department', 'issuer'])
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . mb_strtolower($search) . '%';
                $q->whereHas('employee', function ($eq) use ($like) {
                    $eq->whereRaw('LOWER(first_name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(last_name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(employee_code) LIKE ?', [$like]);
                });
            })
            ->when($status !== '' && in_array($status, ['open', 'resolved', 'escalated'], true), function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->orderByDesc('incident_date')
            ->paginate(20)
            ->withQueryString();

        $employees = Employee::query()->orderBy('employee_code')->get();

        return view('disciplinary-actions.index', [
            'actions' => $actions,
            'employees' => $employees,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'type' => ['required', 'string', 'max:50'],
            'severity' => ['required', 'string', 'in:minor,moderate,major,critical'],
            'incident_date' => ['required', 'date'],
            'description' => ['required', 'string'],
            'action_taken' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,doc,docx'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('disciplinary', 'public');
        }

        $nextId = ((int) DisciplinaryAction::query()->max('id')) + 1;

        DisciplinaryAction::create([
            'id' => $nextId,
            'employee_id' => $validated['employee_id'],
            'type' => $validated['type'],
            'severity' => $validated['severity'],
            'incident_date' => $validated['incident_date'],
            'description' => $validated['description'],
            'action_taken' => $validated['action_taken'] ?? null,
            'status' => 'open',
            'issued_by' => optional($user->employee)->id,
            'attachment_path' => $attachmentPath,
        ]);

        ActivityLogger::log('created', 'DisciplinaryAction', $nextId, "Created disciplinary action for employee #{$validated['employee_id']}");

        return Redirect::route('disciplinary-actions.index')->with('status', 'action-created');
    }

    public function update(Request $request, DisciplinaryAction $disciplinaryAction): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'severity' => ['required', 'string', 'in:minor,moderate,major,critical'],
            'incident_date' => ['required', 'date'],
            'description' => ['required', 'string'],
            'action_taken' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:open,resolved,escalated'],
            'resolution_date' => ['nullable', 'date'],
            'resolution_notes' => ['nullable', 'string'],
        ]);

        $disciplinaryAction->update($validated);

        ActivityLogger::log('updated', 'DisciplinaryAction', $disciplinaryAction->id, "Updated disciplinary action #{$disciplinaryAction->id}");

        return Redirect::route('disciplinary-actions.index')->with('status', 'action-updated');
    }

    public function destroy(Request $request, DisciplinaryAction $disciplinaryAction): RedirectResponse
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $disciplinaryAction->delete();

        ActivityLogger::log('deleted', 'DisciplinaryAction', null, "Deleted disciplinary action");

        return Redirect::route('disciplinary-actions.index')->with('status', 'action-deleted');
    }
}
