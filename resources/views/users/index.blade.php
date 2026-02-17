<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Users') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6">
                <aside class="w-full lg:w-64">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                        <div class="px-4 py-4 border-b bg-gray-50">
                            <div class="text-sm font-semibold text-gray-900">Access</div>
                        </div>

                        <nav class="p-2">
                            @php
                                $activeNav = $activeNav ?? 'users.index';
                                $navItems = [
                                    [
                                        'label' => 'Users',
                                        'route' => 'users.index',
                                    ],
                                    [
                                        'label' => 'Employees',
                                        'route' => 'employees.index',
                                    ],
                                ];
                            @endphp

                            @foreach ($navItems as $item)
                                @php
                                    $isActive = $activeNav === $item['route'];
                                @endphp
                                <a href="{{ route($item['route']) }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium {{ $isActive ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </nav>
                    </div>
                </aside>

                <main class="flex-1">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                        <div class="px-6 py-5 border-b bg-gray-50 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="text-lg font-semibold text-gray-900">User Accounts</div>
                                <div class="text-sm text-gray-600">Login accounts. Users without an employee link are not included in attendance summaries.</div>
                            </div>

                            <form method="GET" class="flex gap-2">
                                <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search name or email"
                                    class="form-control" style="min-width: 240px;" />
                                <button type="submit" class="btn btn-primary">Search</button>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Linked Employee</th>
                                        <th>Department</th>
                                        <th>Roles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($users as $u)
                                        @php
                                            $emp = $u->employee;
                                            $dept = $emp?->department?->name;
                                            $roles = $emp ? $emp->roles->pluck('name')->all() : [];
                                            $roleLabel = count($roles) ? implode(', ', $roles) : '-';
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold">
                                                {{ $u->name }}
                                                @if (!$emp)
                                                    <span class="badge bg-secondary ms-2">No employee link</span>
                                                @endif
                                            </td>
                                            <td>{{ $u->email }}</td>
                                            <td>
                                                @if ($emp)
                                                    {{ $emp->full_name }}
                                                    @if ($emp->employee_code)
                                                        <span class="text-muted">({{ $emp->employee_code }})</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $dept ?: '-' }}</td>
                                            <td>{{ $roleLabel }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-muted fst-italic">No users found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if (method_exists($users, 'links'))
                            <div class="px-4 py-3 border-t">
                                {{ $users->links() }}
                            </div>
                        @endif
                    </div>
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
