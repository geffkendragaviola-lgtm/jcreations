<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Payroll Run') }}</h2>
            <a href="{{ route('payroll.index') }}" class="text-sm text-indigo-700 underline">Back to Payroll</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status') === 'payroll-saved')
                <div class="p-3 rounded bg-green-50 text-green-700 border border-green-200">Payroll saved.</div>
            @endif
            @if (session('status') === 'payroll-approved')
                <div class="p-3 rounded bg-green-50 text-green-700 border border-green-200">Payroll approved.</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div>
                            <div class="text-lg font-semibold">{{ $run->name }}</div>
                            <div class="text-sm text-gray-600">{{ optional($run->period_start)->format('Y-m-d') }} to {{ optional($run->period_end)->format('Y-m-d') }}</div>
                            <div class="text-sm text-gray-600">Status: {{ $run->status }}</div>
                        </div>

                        @if (auth()->user()?->canManageBackoffice() && $run->status !== 'approved')
                            <form method="POST" action="{{ route('payroll.approve', $run) }}" onsubmit="return confirm('Approve this payroll run?');">
                                @csrf
                                @method('patch')
                                <x-primary-button>Approve</x-primary-button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="text-lg font-semibold">Items</div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">Employee</th>
                                    <th class="text-left py-2">Department</th>
                                    <th class="text-left py-2">Gross</th>
                                    <th class="text-left py-2">Deductions</th>
                                    <th class="text-left py-2">Net</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($run->items as $i)
                                    <tr class="border-b">
                                        <td class="py-2">{{ $i->employee_code }} - {{ $i->employee?->full_name }}</td>
                                        <td class="py-2">{{ $i->employee?->department?->name ?? '-' }}</td>
                                        <td class="py-2">{{ number_format((float) $i->gross_pay, 2) }}</td>
                                        <td class="py-2">{{ number_format((float) $i->total_deductions, 2) }}</td>
                                        <td class="py-2 font-semibold">{{ number_format((float) $i->net_pay, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
