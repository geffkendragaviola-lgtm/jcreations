<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Approvals') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="text-lg font-medium text-gray-900">Pending Overtime Requests</div>

                    <div class="mt-4 overflow-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">Employee</th>
                                    <th class="text-left py-2">Date</th>
                                    <th class="text-left py-2">Hours</th>
                                    <th class="text-left py-2">Reason</th>
                                    <th class="text-left py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pendingOvertime as $r)
                                    <tr class="border-b">
                                        <td class="py-2">{{ $r->employee?->full_name }}</td>
                                        <td class="py-2">{{ optional($r->date)->format('Y-m-d') }}</td>
                                        <td class="py-2">{{ $r->hours }}</td>
                                        <td class="py-2">{{ $r->reason }}</td>
                                        <td class="py-2">
                                            <form method="POST" action="{{ route('overtime-requests.approve', $r->id) }}" class="inline">
                                                @csrf
                                                @method('patch')
                                                <x-primary-button>{{ __('Approve') }}</x-primary-button>
                                            </form>
                                            <form method="POST" action="{{ route('overtime-requests.reject', $r->id) }}" class="inline">
                                                @csrf
                                                @method('patch')
                                                <x-danger-button>{{ __('Reject') }}</x-danger-button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="py-3 text-gray-500" colspan="5">No pending overtime requests.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $pendingOvertime->links() }}
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="text-lg font-medium text-gray-900">Pending Absence Requests</div>

                    <div class="mt-4 overflow-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">Employee</th>
                                    <th class="text-left py-2">Dates</th>
                                    <th class="text-left py-2">Type</th>
                                    <th class="text-left py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pendingAbsences as $r)
                                    <tr class="border-b">
                                        <td class="py-2">{{ $r->employee?->full_name }}</td>
                                        <td class="py-2">{{ optional($r->start_date)->format('Y-m-d') }} to {{ optional($r->end_date)->format('Y-m-d') }}</td>
                                        <td class="py-2">{{ $r->leave_type }}</td>
                                        <td class="py-2">
                                            <form method="POST" action="{{ route('leave-requests.approve', $r->id) }}" class="inline">
                                                @csrf
                                                @method('patch')
                                                <x-primary-button>{{ __('Approve') }}</x-primary-button>
                                            </form>
                                            <form method="POST" action="{{ route('leave-requests.reject', $r->id) }}" class="inline">
                                                @csrf
                                                @method('patch')
                                                <x-danger-button>{{ __('Reject') }}</x-danger-button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="py-3 text-gray-500" colspan="4">No pending absence requests.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $pendingAbsences->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
