<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payroll') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <form method="POST" action="{{ route('payroll.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
                    @csrf

                    <div class="md:col-span-6">
                        <x-input-label for="batch_uuid" :value="__('Time Tracking Saved Import')" />
                        <select id="batch_uuid" name="batch_uuid" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                            @forelse ($batches as $b)
                                <option value="{{ $b->uuid }}" {{ (string) $batch_uuid === (string) $b->uuid ? 'selected' : '' }}>
                                    {{ optional($b->date_start)->format('Y-m-d') }} to {{ optional($b->date_end)->format('Y-m-d') }} - {{ $b->source_filename ?? 'Saved Import' }}
                                </option>
                            @empty
                                <option value="">No saved imports yet</option>
                            @endforelse
                        </select>
                    </div>

                    <div>
                        <x-input-label for="mode" :value="__('Period')" />
                        <select id="mode" name="mode" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                            <option value="weekly" {{ $mode === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="biweekly" {{ $mode === 'biweekly' ? 'selected' : '' }}>Bi-weekly</option>
                            <option value="monthly" {{ $mode === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="custom" {{ $mode === 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="start" :value="__('Start')" />
                        <x-text-input id="start" name="start" type="date" class="mt-1 block w-full" :value="old('start', $start)" />
                    </div>

                    <div>
                        <x-input-label for="end" :value="__('End')" />
                        <x-text-input id="end" name="end" type="date" class="mt-1 block w-full" :value="old('end', $end)" />
                    </div>

                    <div>
                        <x-input-label for="base_hours_per_day" :value="__('Hours/Day')" />
                        <x-text-input id="base_hours_per_day" name="base_hours_per_day" type="number" step="0.25" min="1" class="mt-1 block w-full" :value="old('base_hours_per_day', $base_hours_per_day)" />
                    </div>

                    <div>
                        <x-input-label for="ot_multiplier" :value="__('OT Multiplier')" />
                        <x-text-input id="ot_multiplier" name="ot_multiplier" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('ot_multiplier', $ot_multiplier)" />
                    </div>

                    <div>
                        <x-primary-button class="w-full">{{ __('Generate') }}</x-primary-button>
                    </div>

                    <div class="md:col-span-6">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="save_government_deduction" value="1" class="rounded border-gray-300">
                            <span class="text-sm text-gray-700">Save cash advance default</span>
                        </label>
                    </div>

                    @if (count($rows))
                        <div class="md:col-span-6 overflow-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-2">Employee</th>
                                        <th class="text-left py-2">Daily Rate</th>
                                        <th class="text-left py-2">Days Worked</th>
                                        <th class="text-left py-2">Late (hrs)</th>
                                        <th class="text-left py-2">Undertime (hrs)</th>
                                        <th class="text-left py-2">Approved OT (hrs)</th>
                                        <th class="text-left py-2">Approved Absences (days)</th>
                                        <th class="text-left py-2">SSS</th>
                                        <th class="text-left py-2">Pag-IBIG</th>
                                        <th class="text-left py-2">PhilHealth</th>
                                        <th class="text-left py-2">Cash Adv</th>
                                        <th class="text-left py-2">Total Deductions</th>
                                        <th class="text-left py-2">Gross Pay</th>
                                        <th class="text-left py-2">Net Pay</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $r)
                                        <tr class="border-b">
                                            <td class="py-2">{{ $r['employee_code'] }} - {{ $r['employee_name'] }}</td>
                                            <td class="py-2">{{ number_format($r['daily_rate'], 2) }}</td>
                                            <td class="py-2">{{ $r['days_worked'] }}</td>
                                            <td class="py-2">{{ number_format($r['late_hours'], 2) }}</td>
                                            <td class="py-2">{{ number_format($r['undertime_hours'], 2) }}</td>
                                            <td class="py-2">{{ number_format($r['approved_ot_hours'], 2) }}</td>
                                            <td class="py-2">{{ $r['approved_absence_days'] }}</td>
                                            <td class="py-2" style="min-width: 120px;">
                                                {{ number_format($r['sss_deduction'], 2) }}
                                            </td>
                                            <td class="py-2" style="min-width: 120px;">
                                                {{ number_format($r['pagibig_deduction'], 2) }}
                                            </td>
                                            <td class="py-2" style="min-width: 120px;">
                                                {{ number_format($r['philhealth_deduction'], 2) }}
                                            </td>
                                            <td class="py-2" style="min-width: 120px;">
                                                <input type="number" step="0.01" name="cash_advance_deduction[{{ $r['employee_id'] }}]" value="{{ old('cash_advance_deduction.' . $r['employee_id'], $r['cash_advance_deduction']) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                            </td>
                                            <td class="py-2">{{ number_format($r['fixed_deductions_total'], 2) }}</td>
                                            <td class="py-2">{{ number_format($r['gross_pay'], 2) }}</td>
                                            <td class="py-2" style="min-width: 140px;">
                                                <button type="button" class="text-indigo-700 hover:underline font-semibold" x-data="" x-on:click.prevent="$dispatch('open-modal', 'payroll-breakdown-{{ $r['employee_id'] }}')">
                                                    {{ number_format($r['net_pay'], 2) }}
                                                </button>

                                                <x-modal name="payroll-breakdown-{{ $r['employee_id'] }}" :show="false" focusable>
                                                    <div class="p-6 space-y-4">
                                                        <div class="text-lg font-medium text-gray-900">Payroll Breakdown</div>
                                                        <div class="text-sm text-gray-600">{{ $r['employee_code'] }} - {{ $r['employee_name'] }}</div>

                                                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-2">
                                                            <div class="text-sm text-gray-700">
                                                                <span class="font-semibold">Date Range:</span>
                                                                {{ $r['range_start'] ?? '-' }} to {{ $r['range_end'] ?? '-' }}
                                                            </div>
                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-700">
                                                                <div><span class="font-semibold">Daily Rate:</span> {{ number_format($r['daily_rate'], 2) }}</div>
                                                                <div><span class="font-semibold">Hourly Rate:</span> {{ number_format($r['hourly_rate'], 2) }}</div>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                            <div class="rounded-lg border border-gray-200 p-4 space-y-2">
                                                                <div class="text-sm font-semibold text-gray-900">Earnings</div>
                                                                <div class="text-sm text-gray-700 flex justify-between">
                                                                    <span>Gross Pay ({{ $r['days_worked'] }} days)</span>
                                                                    <span class="font-semibold">{{ number_format($r['gross_pay'], 2) }}</span>
                                                                </div>
                                                                <div class="text-sm text-gray-700 flex justify-between">
                                                                    <span>OT Pay ({{ number_format($r['approved_ot_hours'], 2) }} hrs)</span>
                                                                    <span class="font-semibold">{{ number_format($r['ot_pay'], 2) }}</span>
                                                                </div>
                                                            </div>

                                                            <div class="rounded-lg border border-gray-200 p-4 space-y-2">
                                                                <div class="text-sm font-semibold text-gray-900">Penalties</div>
                                                                <div class="text-sm text-gray-700 flex justify-between">
                                                                    <span>Late ({{ number_format($r['late_hours'], 2) }} hrs)</span>
                                                                    <span class="font-semibold">-{{ number_format($r['late_deduction'], 2) }}</span>
                                                                </div>
                                                                <div class="text-sm text-gray-700 flex justify-between">
                                                                    <span>Undertime ({{ number_format($r['undertime_hours'], 2) }} hrs)</span>
                                                                    <span class="font-semibold">-{{ number_format($r['undertime_deduction'], 2) }}</span>
                                                                </div>
                                                                <div class="text-sm text-gray-700 flex justify-between">
                                                                    <span>Absence ({{ $r['approved_absence_days'] }} days)</span>
                                                                    <span class="font-semibold">-{{ number_format($r['absence_deduction'], 2) }}</span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="rounded-lg border border-gray-200 p-4 space-y-2">
                                                            <div class="text-sm font-semibold text-gray-900">Fixed Deductions</div>
                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-700">
                                                                <div class="flex justify-between"><span>SSS</span><span class="font-semibold">-{{ number_format($r['sss_deduction'], 2) }}</span></div>
                                                                <div class="flex justify-between"><span>Pag-IBIG</span><span class="font-semibold">-{{ number_format($r['pagibig_deduction'], 2) }}</span></div>
                                                                <div class="flex justify-between"><span>PhilHealth</span><span class="font-semibold">-{{ number_format($r['philhealth_deduction'], 2) }}</span></div>
                                                                <div class="flex justify-between"><span>Cash Advance</span><span class="font-semibold">-{{ number_format($r['cash_advance_deduction'], 2) }}</span></div>
                                                            </div>
                                                            <div class="text-sm text-gray-900 flex justify-between border-t pt-2">
                                                                <span class="font-semibold">Total Fixed Deductions</span>
                                                                <span class="font-semibold">-{{ number_format($r['fixed_deductions_total'], 2) }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4">
                                                            <div class="text-sm text-gray-900 flex justify-between">
                                                                <span class="font-semibold">Net Pay</span>
                                                                <span class="font-semibold">{{ number_format($r['net_pay'], 2) }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="flex justify-end">
                                                            <x-secondary-button x-on:click="$dispatch('close-modal', 'payroll-breakdown-{{ $r['employee_id'] }}')">Close</x-secondary-button>
                                                        </div>
                                                    </div>
                                                </x-modal>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
