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
                            <span class="text-sm text-gray-700">Save government deduction defaults</span>
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
                                        <th class="text-left py-2">Gov Deduction</th>
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
                                            <td class="py-2" style="min-width: 160px;">
                                                <input type="number" step="0.01" name="government_deduction[{{ $r['employee_id'] }}]" value="{{ old('government_deduction.' . $r['employee_id'], $r['government_deduction']) }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                            </td>
                                            <td class="py-2">{{ number_format($r['net_pay'], 2) }}</td>
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
