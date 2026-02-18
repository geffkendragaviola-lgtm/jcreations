<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Loan Details') }}</h2>
            <a href="{{ route('requests.index') }}" class="text-sm text-indigo-700 underline">Back to Requests</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status') === 'loan-payment-added')
                <div class="p-3 rounded bg-green-50 text-green-700 border border-green-200">
                    Payment recorded.
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="text-sm text-gray-500">Employee</div>
                            <div class="text-lg font-semibold">{{ $loan->employee?->full_name }}</div>
                            <div class="text-sm text-gray-600">{{ $loan->employee?->department?->name }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Status</div>
                            <div class="text-lg font-semibold">{{ ucfirst((string) $loan->status) }}</div>
                            <div class="text-sm text-gray-600">Loan Status: {{ ucfirst((string) $loan->loan_status) }}</div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="text-sm text-gray-500">Released</div>
                            <div class="text-gray-900">
                                @if ($loan->released_at)
                                    {{ optional($loan->released_at)->format('Y-m-d') }}
                                    @if ($loan->releaser)
                                        <span class="text-sm text-gray-600">({{ $loan->releaser?->full_name }})</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-xs text-gray-500">Amount</div>
                            <div class="text-lg font-semibold">₱{{ number_format((float) $loan->amount, 2) }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-xs text-gray-500">Term</div>
                            <div class="text-lg font-semibold">{{ $loan->term_months ? ($loan->term_months . ' months') : '-' }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-xs text-gray-500">Monthly Amortization</div>
                            <div class="text-lg font-semibold">{{ $loan->monthly_amortization !== null ? ('₱' . number_format((float) $loan->monthly_amortization, 2)) : '-' }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-xs text-gray-500">Remaining Balance</div>
                            <div class="text-lg font-semibold">{{ $loan->remaining_balance !== null ? ('₱' . number_format((float) $loan->remaining_balance, 2)) : '-' }}</div>
                            <div class="text-xs text-gray-500">Total Paid: ₱{{ number_format((float) ($loan->total_paid ?? 0), 2) }}</div>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="text-sm text-gray-500">Purpose</div>
                            <div class="text-gray-900">{{ $loan->purpose ?: '-' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Attachment</div>
                            <div>
                                @if ($loan->attachment_path)
                                    <a class="text-indigo-700 underline" href="{{ asset('storage/' . $loan->attachment_path) }}" target="_blank">View Attachment</a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="text-sm text-gray-500">Admin Notes</div>
                        <div class="text-gray-900">{{ $loan->admin_notes ?: '-' }}</div>
                    </div>
                </div>
            </div>

            @if (auth()->user()?->canManageBackoffice())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="text-lg font-semibold">Record a Payment</div>
                        <div class="text-sm text-gray-600">Payments update remaining balance and loan status.</div>

                        <form method="POST" action="{{ route('loan-requests.payments.store', $loan->id) }}" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            @csrf

                            <div>
                                <x-input-label for="payment_amount" :value="__('Amount')" />
                                <x-text-input id="payment_amount" name="amount" type="number" min="0.01" step="0.01" class="mt-1 block w-full" required />
                                <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="payment_date" :value="__('Payment Date')" />
                                <x-text-input id="payment_date" name="payment_date" type="date" class="mt-1 block w-full" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required />
                                <x-input-error :messages="$errors->get('payment_date')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="payment_notes" :value="__('Notes (Optional)')" />
                                <x-text-input id="payment_notes" name="notes" type="text" class="mt-1 block w-full" value="{{ old('notes') }}" />
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>

                            <div class="md:col-span-3 flex justify-end">
                                <x-primary-button>Save Payment</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="text-lg font-semibold">Payment History</div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">Date</th>
                                    <th class="text-left py-2">Amount</th>
                                    <th class="text-left py-2">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($loan->payments as $p)
                                    <tr class="border-b">
                                        <td class="py-2">{{ optional($p->payment_date)->format('Y-m-d') }}</td>
                                        <td class="py-2">₱{{ number_format((float) $p->amount, 2) }}</td>
                                        <td class="py-2">{{ $p->notes ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="py-3 text-gray-600" colspan="3">No payments recorded.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
