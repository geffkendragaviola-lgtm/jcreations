<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user?->canManageBackoffice()) {
            abort(403);
        }

        $q = trim((string) $request->query('q', ''));

        $users = User::query()
            ->with(['employee.department', 'employee.roles'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner
                        ->where('name', 'like', '%' . $q . '%')
                        ->orWhere('email', 'like', '%' . $q . '%');
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $employees = Employee::query()
            ->whereDoesntHave('user')
            ->orderBy('first_name')
            ->get();

        $roles = Role::query()->orderBy('name')->get();

        return view('users.index', [
            'users' => $users,
            'q' => $q,
            'employees' => $employees,
            'roles' => $roles,
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
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        $newUser = DB::transaction(function () use ($validated) {
            $newUser = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'employee_id' => $validated['employee_id'] ?? null,
            ]);

            if (!empty($validated['roles']) && $newUser->employee) {
                $newUser->employee->roles()->sync($validated['roles']);
            }

            return $newUser;
        });

        ActivityLogger::log('created', 'User', $newUser->id, "Created user {$newUser->name}");

        return Redirect::route('users.index')->with('status', 'user-created');
    }

    public function edit(Request $request, User $user)
    {
        $authUser = $request->user();
        if (!$authUser?->canManageBackoffice()) {
            abort(403);
        }

        $user->load(['employee.roles']);

        $employees = Employee::query()
            ->where(function ($q) use ($user) {
                $q->whereDoesntHave('user')
                    ->orWhere('id', $user->employee_id);
            })
            ->orderBy('first_name')
            ->get();

        $roles = Role::query()->orderBy('name')->get();

        return view('users.edit', [
            'editUser' => $user,
            'employees' => $employees,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $authUser = $request->user();
        if (!$authUser?->canManageBackoffice()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        DB::transaction(function () use ($user, $validated) {
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->employee_id = $validated['employee_id'] ?? null;

            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            if ($user->employee) {
                $user->employee->roles()->sync($validated['roles'] ?? []);
            }
        });

        ActivityLogger::log('updated', 'User', $user->id, "Updated user {$user->name}");

        return Redirect::route('users.index')->with('status', 'user-updated');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $authUser = $request->user();
        if (!$authUser?->canManageBackoffice()) {
            abort(403);
        }

        if ($user->id === $authUser->id) {
            return Redirect::route('users.index')->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $userName = $user->name;

        DB::transaction(function () use ($user) {
            if ($user->employee) {
                $user->employee->roles()->detach();
            }
            $user->delete();
        });

        ActivityLogger::log('deleted', 'User', null, "Deleted user {$userName}");

        return Redirect::route('users.index')->with('status', 'user-deleted');
    }
}
