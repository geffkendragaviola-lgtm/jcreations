<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Cash Advance Details') }}</h2>
            <a href="{{ route('requests.index') }}" class="text-sm text-indigo-700 underline">Back to Requests</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="text-sm text-gray-500">Employee</div>
                            <div class="text-lg font-semibold">{{ $cashAdvance->employee?->full_name }}</div>
                            <div class="text-sm text-gray-600">{{ $cashAdvance->employee?->department?->name }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Status</div>
                            <div class="text-lg font-semibold">{{ ucfirst((string) $cashAdvance->status) }}</div>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-xs text-gray-500">Amount</div>
                            <div class="text-lg font-semibold">₱{{ number_format((float) $cashAdvance->amount, 2) }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-xs text-gray-500">Approved</div>
                            <div class="text-gray-900">
                                @if ($cashAdvance->approved_at)
                                    {{ optional($cashAdvance->approved_at)->format('Y-m-d') }}
                                    @if ($cashAdvance->approver)
                                        <span class="text-sm text-gray-600">({{ $cashAdvance->approver?->full_name }})</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-xs text-gray-500">Released (Given)</div>
                            <div class="text-gray-900">
                                @if ($cashAdvance->released_at)
                                    {{ optional($cashAdvance->released_at)->format('Y-m-d') }}
                                    @if ($cashAdvance->releaser)
                                        <span class="text-sm text-gray-600">({{ $cashAdvance->releaser?->full_name }})</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-xs text-gray-500">Deducted in Payroll</div>
                            <div class="text-gray-900">
                                @if ($cashAdvance->deducted_at)
                                    {{ optional($cashAdvance->deducted_at)->format('Y-m-d') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-xs text-gray-500">Payroll Run ID</div>
                            <div class="text-gray-900">{{ $cashAdvance->deducted_payroll_run_id ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="text-sm text-gray-500">Reason</div>
                            <div class="text-gray-900">{{ $cashAdvance->reason ?: '-' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Attachment</div>
                            <div>
                                @if ($cashAdvance->attachment_path)
                                    <a class="text-indigo-700 underline" href="{{ asset('storage/' . $cashAdvance->attachment_path) }}" target="_blank">View Attachment</a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="text-sm text-gray-500">Admin Notes</div>
                        <div class="text-gray-900">{{ $cashAdvance->admin_notes ?: '-' }}</div>
                    </div>

                    @if (auth()->user()?->canManageBackoffice() && $cashAdvance->status === 'approved' && !$cashAdvance->released_at)
                        <div class="mt-6">
                            <form method="POST" action="{{ route('cash-advance-requests.release', $cashAdvance->id) }}">
                                @csrf
                                @method('patch')
                                <x-primary-button>Mark as Given</x-primary-button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
