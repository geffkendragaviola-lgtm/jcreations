<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Requests') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between" @if(!auth()->user()?->canManageBackoffice()) x-data="{ requestType: '{{ old('_request_type') ?: 'absence' }}' }" x-init="@if(old('_request_type')) $dispatch('open-modal', 'create-request'); @endif" @endif>
                    <form method="GET" action="{{ route('requests.index') }}" class="flex gap-2 items-end">
                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="" {{ ($filters['status'] ?? '') === '' ? 'selected' : '' }}>All</option>
                                <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <x-primary-button>{{ __('Filter') }}</x-primary-button>
                    </form>

                    @if (!auth()->user()?->canManageBackoffice())
                        <div class="flex justify-end">
                            <x-primary-button type="button" x-data x-on:click="$dispatch('open-modal', 'create-request')">{{ __('Create Request') }}</x-primary-button>
                        </div>

                        <x-modal name="create-request" :show="false" focusable>
                            <div class="p-6 space-y-4" x-data>
                                <div>
                                    <div class="text-lg font-medium text-gray-900">{{ __('New Request') }}</div>
                                    <div class="text-sm text-gray-600">Select a type. The form will adjust based on your selection.</div>
                                </div>

                                <div>
                                    <x-input-label for="cr_request_type" :value="__('Request Type')" />
                                    <select id="cr_request_type" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" x-model="requestType">
                                        <option value="absence">Absence (Time Off)</option>
                                        <option value="absence_notice">Unfiled Absence (Justification)</option>
                                        <option value="overtime">Overtime</option>
                                        <option value="late">Late / Undertime / Missed Logs</option>
                                        <option value="cash_advance">Cash Advance</option>
                                        <option value="loan">Loan</option>
                                    </select>
                                </div>

                                <div x-show="requestType === 'absence'" x-cloak>
                                    <form method="POST" action="{{ route('leave-requests.store') }}" class="space-y-4" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="_request_type" value="absence" />

                                        <div>
                                            <x-input-label for="cr_leave_type" :value="__('Type')" />
                                            <x-text-input id="cr_leave_type" name="leave_type" type="text" class="mt-1 block w-full" value="leave without pay" />
                                            <x-input-error class="mt-2" :messages="$errors->get('leave_type')" />
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div>
                                                <x-input-label for="cr_start_date" :value="__('Start Date')" />
                                                <x-text-input id="cr_start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date')" required />
                                                <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
                                            </div>
                                            <div>
                                                <x-input-label for="cr_end_date" :value="__('End Date')" />
                                                <x-text-input id="cr_end_date" name="end_date" type="date" class="mt-1 block w-full" :value="old('end_date')" required />
                                                <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
                                            </div>
                                        </div>
                                        <div>
                                            <x-input-label for="cr_day_type" :value="__('Duration Type')" />
                                            <select id="cr_day_type" name="day_type" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                                <option value="full_day" {{ old('day_type') === 'full_day' ? 'selected' : '' }}>Full Day</option>
                                                <option value="half_day" {{ old('day_type') === 'half_day' ? 'selected' : '' }}>Half Day</option>
                                            </select>
                                            <x-input-error class="mt-2" :messages="$errors->get('day_type')" />
                                        </div>
                                        <div>
                                            <x-input-label for="cr_leave_description" :value="__('Description / Reason')" />
                                            <x-text-input id="cr_leave_description" name="description" type="text" class="mt-1 block w-full" :value="old('description')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                                        </div>
                                        <div>
                                            <x-input-label for="cr_leave_attachment" :value="__('Attachment (Optional)')" />
                                            <input id="cr_leave_attachment" name="attachment" type="file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" class="mt-1 block w-full" />
                                            <x-input-error class="mt-2" :messages="$errors->get('attachment')" />
                                        </div>

                                        <div class="flex justify-end gap-2">
                                            <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'create-request')">Cancel</x-secondary-button>
                                            <x-primary-button>{{ __('Submit') }}</x-primary-button>
                                        </div>
                                    </form>
                                </div>

                                <div x-show="requestType === 'absence_notice'" x-cloak>
                                    <form method="POST" action="{{ route('absence-notices.store') }}" class="space-y-4" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="_request_type" value="absence_notice" />

                                        <div class="text-sm text-gray-600">File a justification for a date that was detected as ABSENT in time tracking.</div>
                                        <div>
                                            <x-input-label for="cr_absence_notice_date" :value="__('Date')" />
                                            <x-text-input id="cr_absence_notice_date" name="date" type="date" class="mt-1 block w-full" :value="old('date')" required />
                                            <x-input-error class="mt-2" :messages="$errors->get('date')" />
                                        </div>
                                        <div>
                                            <x-input-label for="cr_absence_notice_reason" :value="__('Reason')" />
                                            <x-text-input id="cr_absence_notice_reason" name="reason" type="text" class="mt-1 block w-full" :value="old('reason')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                                        </div>
                                        <div>
                                            <x-input-label for="cr_absence_notice_attachment" :value="__('Attachment (Optional)')" />
                                            <input id="cr_absence_notice_attachment" name="attachment" type="file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" class="mt-1 block w-full" />
                                            <x-input-error class="mt-2" :messages="$errors->get('attachment')" />
                                        </div>

                                        <div class="flex justify-end gap-2">
                                            <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'create-request')">Cancel</x-secondary-button>
                                            <x-primary-button>{{ __('Submit') }}</x-primary-button>
                                        </div>
                                    </form>
                                </div>

                                <div x-show="requestType === 'overtime'" x-cloak>
                                    <form method="POST" action="{{ route('overtime-requests.store') }}" class="space-y-4" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="_request_type" value="overtime" />

                                        <div class="text-sm text-gray-600">You can file ahead. If logs exist, hours may be computed automatically; otherwise, enter hours.</div>
                                        <div>
                                            <x-input-label for="cr_ot_date" :value="__('Date')" />
                                            <x-text-input id="cr_ot_date" name="date" type="date" class="mt-1 block w-full" :value="old('date')" required />
                                            <x-input-error class="mt-2" :messages="$errors->get('date')" />
                                        </div>
                                        <div>
                                            <x-input-label for="cr_ot_hours" :value="__('Hours')" />
                                            <x-text-input id="cr_ot_hours" name="hours" type="number" step="0.25" min="0.25" class="mt-1 block w-full" :value="old('hours')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('hours')" />
                                        </div>
                                        <div>
                                            <x-input-label for="cr_ot_description" :value="__('Description')" />
                                            <x-text-input id="cr_ot_description" name="description" type="text" class="mt-1 block w-full" :value="old('description')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                                        </div>
                                        <div>
                                            <x-input-label for="cr_ot_reason" :value="__('Reason')" />
                                            <x-text-input id="cr_ot_reason" name="reason" type="text" class="mt-1 block w-full" :value="old('reason')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                                        </div>
                                        <div>
                                            <x-input-label for="cr_ot_attachment" :value="__('Attachment (Optional)')" />
                                            <input id="cr_ot_attachment" name="attachment" type="file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" class="mt-1 block w-full" />
                                            <x-input-error class="mt-2" :messages="$errors->get('attachment')" />
                                        </div>

                                        <div class="flex justify-end gap-2">
                                            <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'create-request')">Cancel</x-secondary-button>
                                            <x-primary-button>{{ __('Submit') }}</x-primary-button>
                                        </div>
                                    </form>
                                </div>

                                <div x-show="requestType === 'late'" x-cloak>
                                    <form method="POST" action="{{ route('late-requests.store') }}" class="space-y-4" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="_request_type" value="late" />

                                        <div class="text-sm text-gray-600">This request is validated against time tracking logs.</div>
                                        <div>
                                            <x-input-label for="cr_lu_date" :value="__('Date')" />
                                            <x-text-input id="cr_lu_date" name="date" type="date" class="mt-1 block w-full" :value="old('date')" required />
                                            <x-input-error class="mt-2" :messages="$errors->get('date')" />
                                        </div>
                                        <div>
                                            <x-input-label for="cr_lu_type" :value="__('Type')" />
                                            <select id="cr_lu_type" name="type" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                                <option value="late" {{ old('type') === 'late' ? 'selected' : '' }}>Late</option>
                                                <option value="undertime" {{ old('type') === 'undertime' ? 'selected' : '' }}>Undertime</option>
                                                <option value="missed_logs" {{ old('type') === 'missed_logs' ? 'selected' : '' }}>Missed Logs</option>
                                            </select>
                                            <x-input-error class="mt-2" :messages="$errors->get('type')" />
                                        </div>
                                        <div>
                                            <x-input-label for="cr_lu_reason" :value="__('Reason')" />
                                            <x-text-input id="cr_lu_reason" name="reason" type="text" class="mt-1 block w-full" :value="old('reason')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                                        </div>
                                        <div>
                                            <x-input-label for="cr_lu_attachment" :value="__('Attachment (Optional)')" />
                                            <input id="cr_lu_attachment" name="attachment" type="file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" class="mt-1 block w-full" />
                                            <x-input-error class="mt-2" :messages="$errors->get('attachment')" />
                                        </div>

                                        <div class="flex justify-end gap-2">
                                            <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'create-request')">Cancel</x-secondary-button>
                                            <x-primary-button>{{ __('Submit') }}</x-primary-button>
                                        </div>
                                    </form>
                                </div>

                                <div x-show="requestType === 'cash_advance'" x-cloak>
                                    <form method="POST" action="{{ route('cash-advance-requests.store') }}" class="space-y-4" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="_request_type" value="cash_advance" />

                                        <div class="text-sm text-gray-600">Request a cash advance. You can attach supporting files (PDF/images/docs).</div>
                                        <div>
                                            <x-input-label for="cr_ca_amount" :value="__('Amount')" />
                                            <x-text-input id="cr_ca_amount" name="amount" type="number" step="0.01" min="1" class="mt-1 block w-full" :value="old('amount')" required />
                                            <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                                        </div>
                                        <div>
                                            <x-input-label for="cr_ca_reason" :value="__('Reason (Optional)')" />
                                            <x-text-input id="cr_ca_reason" name="reason" type="text" class="mt-1 block w-full" :value="old('reason')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                                        </div>
                                        <div>
                                            <x-input-label for="cr_ca_attachment" :value="__('Attachment (Optional)')" />
                                            <input id="cr_ca_attachment" name="attachment" type="file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" class="mt-1 block w-full" />
                                            <x-input-error class="mt-2" :messages="$errors->get('attachment')" />
                                        </div>

                                        <div class="flex justify-end gap-2">
                                            <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'create-request')">Cancel</x-secondary-button>
                                            <x-primary-button>{{ __('Submit') }}</x-primary-button>
                                        </div>
                                    </form>
                                </div>

                                <div x-show="requestType === 'loan'" x-cloak>
                                    <form method="POST" action="{{ route('loan-requests.store') }}" class="space-y-4" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="_request_type" value="loan" />

                                        <div class="text-sm text-gray-600">Request a loan. Provide amount and (optional) terms/purpose.</div>
                                        <div>
                                            <x-input-label for="cr_loan_amount" :value="__('Amount')" />
                                            <x-text-input id="cr_loan_amount" name="amount" type="number" step="0.01" min="1" class="mt-1 block w-full" :value="old('amount')" required />
                                            <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                                        </div>
                                        <div>
                                            <x-input-label for="cr_loan_term_months" :value="__('Term (Months) (Optional)')" />
                                            <x-text-input id="cr_loan_term_months" name="term_months" type="number" min="1" step="1" class="mt-1 block w-full" :value="old('term_months')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('term_months')" />
                                        </div>
                                        <div>
                                            <x-input-label for="cr_loan_purpose" :value="__('Purpose (Optional)')" />
                                            <x-text-input id="cr_loan_purpose" name="purpose" type="text" class="mt-1 block w-full" :value="old('purpose')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('purpose')" />
                                        </div>
                                        <div>
                                            <x-input-label for="cr_loan_attachment" :value="__('Attachment (Optional)')" />
                                            <input id="cr_loan_attachment" name="attachment" type="file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" class="mt-1 block w-full" />
                                            <x-input-error class="mt-2" :messages="$errors->get('attachment')" />
                                        </div>

                                        <div class="flex justify-end gap-2">
                                            <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'create-request')">Cancel</x-secondary-button>
                                            <x-primary-button>{{ __('Submit') }}</x-primary-button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </x-modal>
                    @endif
                </div>

                <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700">
                    <div class="font-semibold text-gray-900">Notes</div>
                    <div class="mt-1">
                        <div>Absence and Overtime can be filed ahead of time.</div>
                        <div>Late / Undertime / Missed Logs must be based on time tracking logs.</div>
                    </div>
                </div>
            </div>

            @if (auth()->user()?->canManageBackoffice() && !empty($pendingInbox))
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Pending Inbox') }}</h3>
                            <div class="mt-1 text-sm text-gray-600">Quick actions for requests that need approval.</div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('requests.index', array_filter(['status' => 'pending'])) }}" class="px-3 py-2 rounded border bg-indigo-600 text-white border-indigo-600">View All Pending</a>
                            <a href="{{ route('requests.index', array_filter(['status' => ''])) }}" class="px-3 py-2 rounded border bg-white text-gray-700 border-gray-200">Clear Pending Filter</a>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">Pending Absence</div>
                                    <div class="text-xs text-gray-500">Leave / time off requests</div>
                                </div>
                                <div class="text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-900 font-semibold">{{ (int) ($pendingInbox['counts']['absence'] ?? 0) }}</div>
                            </div>

                            <div class="mt-3 space-y-2 max-h-64 overflow-auto pr-1">
                                @forelse (($pendingInbox['absence'] ?? []) as $r)
                                    <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-semibold text-gray-900">{{ $r->employee?->full_name }}</div>
                                                <div class="text-xs text-gray-600">{{ optional($r->start_date)->format('Y-m-d') }} to {{ optional($r->end_date)->format('Y-m-d') }}</div>
                                            </div>
                                            <div class="shrink-0 flex gap-2">
                                                <x-secondary-button type="button" x-data x-on:click="$dispatch('open-modal', 'approve-leave-{{ $r->id }}')">Approve</x-secondary-button>
                                                <x-danger-button type="button" x-data x-on:click="$dispatch('open-modal', 'reject-leave-{{ $r->id }}')">Reject</x-danger-button>
                                            </div>
                                        </div>
                                    </div>

                                    <x-modal name="approve-leave-{{ $r->id }}" :show="false" focusable>
                                        <form method="POST" action="{{ route('leave-requests.approve', $r->id) }}" class="p-6 space-y-4">
                                            @csrf
                                            @method('patch')

                                            <div class="text-lg font-medium text-gray-900">Approve Absence Request</div>
                                            <div class="text-sm text-gray-600">Optional: add notes for the employee.</div>

                                            <div>
                                                <x-input-label for="leave_admin_notes_approve_inbox_{{ $r->id }}" :value="__('Admin Notes (Optional)')" />
                                                <textarea id="leave_admin_notes_approve_inbox_{{ $r->id }}" name="admin_notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                            </div>

                                            <div class="flex justify-end gap-2">
                                                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'approve-leave-{{ $r->id }}')">Cancel</x-secondary-button>
                                                <x-primary-button>Approve</x-primary-button>
                                            </div>
                                        </form>
                                    </x-modal>

                                    <x-modal name="reject-leave-{{ $r->id }}" :show="false" focusable>
                                        <form method="POST" action="{{ route('leave-requests.reject', $r->id) }}" class="p-6 space-y-4">
                                            @csrf
                                            @method('patch')

                                            <div class="text-lg font-medium text-gray-900">Reject Absence Request</div>
                                            <div class="text-sm text-gray-600">Optional: add rejection reason/notes for the employee.</div>

                                            <div>
                                                <x-input-label for="leave_admin_notes_reject_inbox_{{ $r->id }}" :value="__('Admin Notes (Optional)')" />
                                                <textarea id="leave_admin_notes_reject_inbox_{{ $r->id }}" name="admin_notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                            </div>

                                            <div class="flex justify-end gap-2">
                                                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'reject-leave-{{ $r->id }}')">Cancel</x-secondary-button>
                                                <x-danger-button>Reject</x-danger-button>
                                            </div>
                                        </form>
                                    </x-modal>
                                @empty
                                    <div class="text-sm text-gray-500">No pending absence requests.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">Pending Overtime</div>
                                    <div class="text-xs text-gray-500">OT requests</div>
                                </div>
                                <div class="text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-900 font-semibold">{{ (int) ($pendingInbox['counts']['overtime'] ?? 0) }}</div>
                            </div>

                            <div class="mt-3 space-y-2 max-h-64 overflow-auto pr-1">
                                @forelse (($pendingInbox['overtime'] ?? []) as $r)
                                    <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-semibold text-gray-900">{{ $r->employee?->full_name }}</div>
                                                <div class="text-xs text-gray-600">{{ optional($r->date)->format('Y-m-d') }} · {{ $r->hours }}h</div>
                                            </div>
                                            <div class="shrink-0 flex gap-2">
                                                <x-secondary-button type="button" x-data x-on:click="$dispatch('open-modal', 'approve-ot-{{ $r->id }}')">Approve</x-secondary-button>
                                                <x-danger-button type="button" x-data x-on:click="$dispatch('open-modal', 'reject-ot-{{ $r->id }}')">Reject</x-danger-button>
                                            </div>
                                        </div>
                                    </div>

                                    <x-modal name="approve-ot-{{ $r->id }}" :show="false" focusable>
                                        <form method="POST" action="{{ route('overtime-requests.approve', $r->id) }}" class="p-6 space-y-4">
                                            @csrf
                                            @method('patch')

                                            <div class="text-lg font-medium text-gray-900">Approve Overtime Request</div>
                                            <div class="text-sm text-gray-600">Optional: add notes for the employee.</div>

                                            <div>
                                                <x-input-label for="ot_admin_notes_approve_inbox_{{ $r->id }}" :value="__('Admin Notes (Optional)')" />
                                                <textarea id="ot_admin_notes_approve_inbox_{{ $r->id }}" name="admin_notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                            </div>

                                            <div class="flex justify-end gap-2">
                                                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'approve-ot-{{ $r->id }}')">Cancel</x-secondary-button>
                                                <x-primary-button>Approve</x-primary-button>
                                            </div>
                                        </form>
                                    </x-modal>

                                    <x-modal name="reject-ot-{{ $r->id }}" :show="false" focusable>
                                        <form method="POST" action="{{ route('overtime-requests.reject', $r->id) }}" class="p-6 space-y-4">
                                            @csrf
                                            @method('patch')

                                            <div class="text-lg font-medium text-gray-900">Reject Overtime Request</div>
                                            <div class="text-sm text-gray-600">Optional: add rejection reason/notes for the employee.</div>

                                            <div>
                                                <x-input-label for="ot_admin_notes_reject_inbox_{{ $r->id }}" :value="__('Admin Notes (Optional)')" />
                                                <textarea id="ot_admin_notes_reject_inbox_{{ $r->id }}" name="admin_notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                            </div>

                                            <div class="flex justify-end gap-2">
                                                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'reject-ot-{{ $r->id }}')">Cancel</x-secondary-button>
                                                <x-danger-button>Reject</x-danger-button>
                                            </div>
                                        </form>
                                    </x-modal>
                                @empty
                                    <div class="text-sm text-gray-500">No pending overtime requests.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">Pending Late / Undertime</div>
                                    <div class="text-xs text-gray-500">Late / undertime / missed logs</div>
                                </div>
                                <div class="text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-900 font-semibold">{{ (int) ($pendingInbox['counts']['late'] ?? 0) }}</div>
                            </div>

                            <div class="mt-3 space-y-2 max-h-64 overflow-auto pr-1">
                                @forelse (($pendingInbox['late'] ?? []) as $r)
                                    <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-semibold text-gray-900">{{ $r->employee?->full_name }}</div>
                                                <div class="text-xs text-gray-600">{{ optional($r->date)->format('Y-m-d') }} · {{ $r->type }} · {{ $r->minutes }}m</div>
                                            </div>
                                            <div class="shrink-0 flex gap-2">
                                                <x-secondary-button type="button" x-data x-on:click="$dispatch('open-modal', 'approve-late-{{ $r->id }}')">Approve</x-secondary-button>
                                                <x-danger-button type="button" x-data x-on:click="$dispatch('open-modal', 'reject-late-{{ $r->id }}')">Reject</x-danger-button>
                                            </div>
                                        </div>
                                    </div>

                                    <x-modal name="approve-late-{{ $r->id }}" :show="false" focusable>
                                        <form method="POST" action="{{ route('late-requests.approve', $r->id) }}" class="p-6 space-y-4">
                                            @csrf
                                            @method('patch')

                                            <div class="text-lg font-medium text-gray-900">Approve Late / Undertime / Missed Logs Request</div>
                                            <div class="text-sm text-gray-600">Optional: add notes for the employee.</div>

                                            <div>
                                                <x-input-label for="late_admin_notes_approve_inbox_{{ $r->id }}" :value="__('Admin Notes (Optional)')" />
                                                <textarea id="late_admin_notes_approve_inbox_{{ $r->id }}" name="admin_notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                            </div>

                                            <div class="flex justify-end gap-2">
                                                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'approve-late-{{ $r->id }}')">Cancel</x-secondary-button>
                                                <x-primary-button>Approve</x-primary-button>
                                            </div>
                                        </form>
                                    </x-modal>

                                    <x-modal name="reject-late-{{ $r->id }}" :show="false" focusable>
                                        <form method="POST" action="{{ route('late-requests.reject', $r->id) }}" class="p-6 space-y-4">
                                            @csrf
                                            @method('patch')

                                            <div class="text-lg font-medium text-gray-900">Reject Late / Undertime / Missed Logs Request</div>
                                            <div class="text-sm text-gray-600">Optional: add rejection reason/notes for the employee.</div>

                                            <div>
                                                <x-input-label for="late_admin_notes_reject_inbox_{{ $r->id }}" :value="__('Admin Notes (Optional)')" />
                                                <textarea id="late_admin_notes_reject_inbox_{{ $r->id }}" name="admin_notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                            </div>

                                            <div class="flex justify-end gap-2">
                                                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'reject-late-{{ $r->id }}')">Cancel</x-secondary-button>
                                                <x-danger-button>Reject</x-danger-button>
                                            </div>
                                        </form>
                                    </x-modal>
                                @empty
                                    <div class="text-sm text-gray-500">No pending late/undertime requests.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">Pending Cash Advance</div>
                                    <div class="text-xs text-gray-500">Financial request</div>
                                </div>
                                <div class="text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-900 font-semibold">{{ (int) ($pendingInbox['counts']['cash_advance'] ?? 0) }}</div>
                            </div>

                            <div class="mt-3 space-y-2 max-h-64 overflow-auto pr-1">
                                @forelse (($pendingInbox['cash_advance'] ?? []) as $r)
                                    <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-semibold text-gray-900">{{ $r->employee?->full_name }}</div>
                                                <div class="text-xs text-gray-600">₱{{ number_format((float) $r->amount, 2) }}</div>
                                            </div>
                                            <div class="shrink-0 flex gap-2">
                                                <x-secondary-button type="button" x-data x-on:click="$dispatch('open-modal', 'approve-cash-advance-{{ $r->id }}')">Approve</x-secondary-button>
                                                <x-danger-button type="button" x-data x-on:click="$dispatch('open-modal', 'reject-cash-advance-{{ $r->id }}')">Reject</x-danger-button>
                                            </div>
                                        </div>
                                    </div>

                                    <x-modal name="approve-cash-advance-{{ $r->id }}" :show="false" focusable>
                                        <form method="POST" action="{{ route('cash-advance-requests.approve', $r->id) }}" class="p-6 space-y-4">
                                            @csrf
                                            @method('patch')

                                            <div class="text-lg font-medium text-gray-900">Approve Cash Advance Request</div>
                                            <div class="text-sm text-gray-600">Optional: add notes for the employee.</div>

                                            <div>
                                                <x-input-label for="cash_advance_admin_notes_approve_inbox_{{ $r->id }}" :value="__('Admin Notes (Optional)')" />
                                                <textarea id="cash_advance_admin_notes_approve_inbox_{{ $r->id }}" name="admin_notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                            </div>

                                            <div class="flex justify-end gap-2">
                                                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'approve-cash-advance-{{ $r->id }}')">Cancel</x-secondary-button>
                                                <x-primary-button>Approve</x-primary-button>
                                            </div>
                                        </form>
                                    </x-modal>

                                    <x-modal name="reject-cash-advance-{{ $r->id }}" :show="false" focusable>
                                        <form method="POST" action="{{ route('cash-advance-requests.reject', $r->id) }}" class="p-6 space-y-4">
                                            @csrf
                                            @method('patch')

                                            <div class="text-lg font-medium text-gray-900">Reject Cash Advance Request</div>
                                            <div class="text-sm text-gray-600">Optional: add rejection reason/notes for the employee.</div>

                                            <div>
                                                <x-input-label for="cash_advance_admin_notes_reject_inbox_{{ $r->id }}" :value="__('Admin Notes (Optional)')" />
                                                <textarea id="cash_advance_admin_notes_reject_inbox_{{ $r->id }}" name="admin_notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                            </div>

                                            <div class="flex justify-end gap-2">
                                                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'reject-cash-advance-{{ $r->id }}')">Cancel</x-secondary-button>
                                                <x-danger-button>Reject</x-danger-button>
                                            </div>
                                        </form>
                                    </x-modal>
                                @empty
                                    <div class="text-sm text-gray-500">No pending cash advance requests.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">Pending Loan</div>
                                    <div class="text-xs text-gray-500">Financial request</div>
                                </div>
                                <div class="text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-900 font-semibold">{{ (int) ($pendingInbox['counts']['loan'] ?? 0) }}</div>
                            </div>

                            <div class="mt-3 space-y-2 max-h-64 overflow-auto pr-1">
                                @forelse (($pendingInbox['loan'] ?? []) as $r)
                                    <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-semibold text-gray-900">{{ $r->employee?->full_name }}</div>
                                                <div class="text-xs text-gray-600">₱{{ number_format((float) $r->amount, 2) }}@if ($r->term_months) · {{ $r->term_months }}mo @endif</div>
                                            </div>
                                            <div class="shrink-0 flex gap-2">
                                                <x-secondary-button type="button" x-data x-on:click="$dispatch('open-modal', 'approve-loan-{{ $r->id }}')">Approve</x-secondary-button>
                                                <x-danger-button type="button" x-data x-on:click="$dispatch('open-modal', 'reject-loan-{{ $r->id }}')">Reject</x-danger-button>
                                            </div>
                                        </div>
                                    </div>

                                    <x-modal name="approve-loan-{{ $r->id }}" :show="false" focusable>
                                        <form method="POST" action="{{ route('loan-requests.approve', $r->id) }}" class="p-6 space-y-4">
                                            @csrf
                                            @method('patch')

                                            <div class="text-lg font-medium text-gray-900">Approve Loan Request</div>
                                            <div class="text-sm text-gray-600">Optional: add notes for the employee.</div>

                                            <div>
                                                <x-input-label for="loan_admin_notes_approve_inbox_{{ $r->id }}" :value="__('Admin Notes (Optional)')" />
                                                <textarea id="loan_admin_notes_approve_inbox_{{ $r->id }}" name="admin_notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                            </div>

                                            <div class="flex justify-end gap-2">
                                                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'approve-loan-{{ $r->id }}')">Cancel</x-secondary-button>
                                                <x-primary-button>Approve</x-primary-button>
                                            </div>
                                        </form>
                                    </x-modal>

                                    <x-modal name="reject-loan-{{ $r->id }}" :show="false" focusable>
                                        <form method="POST" action="{{ route('loan-requests.reject', $r->id) }}" class="p-6 space-y-4">
                                            @csrf
                                            @method('patch')

                                            <div class="text-lg font-medium text-gray-900">Reject Loan Request</div>
                                            <div class="text-sm text-gray-600">Optional: add rejection reason/notes for the employee.</div>

                                            <div>
                                                <x-input-label for="loan_admin_notes_reject_inbox_{{ $r->id }}" :value="__('Admin Notes (Optional)')" />
                                                <textarea id="loan_admin_notes_reject_inbox_{{ $r->id }}" name="admin_notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                            </div>

                                            <div class="flex justify-end gap-2">
                                                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'reject-loan-{{ $r->id }}')">Cancel</x-secondary-button>
                                                <x-danger-button>Reject</x-danger-button>
                                            </div>
                                        </form>
                                    </x-modal>
                                @empty
                                    <div class="text-sm text-gray-500">No pending loan requests.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    @if (!empty($forRelease))
                        <div class="mt-4">
                            <div class="text-sm font-semibold text-gray-900">For Release (Given)</div>
                            <div class="text-xs text-gray-500">Approved requests that are not yet released. Release first, then it will be deducted on the next payroll.</div>

                            <div class="mt-3 grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">Cash Advance</div>
                                            <div class="text-xs text-gray-500">Approved but not yet given</div>
                                        </div>
                                        <div class="text-xs px-2 py-1 rounded-full bg-indigo-100 text-indigo-900 font-semibold">{{ (int) ($forRelease['counts']['cash_advance'] ?? 0) }}</div>
                                    </div>

                                    <div class="mt-3 space-y-2 max-h-64 overflow-auto pr-1">
                                        @forelse (($forRelease['cash_advance'] ?? []) as $r)
                                            <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div class="min-w-0">
                                                        <div class="truncate text-sm font-semibold text-gray-900">{{ $r->employee?->full_name }}</div>
                                                        <div class="text-xs text-gray-600">₱{{ number_format((float) $r->amount, 2) }}</div>
                                                    </div>
                                                    <div class="shrink-0">
                                                        <form method="POST" action="{{ route('cash-advance-requests.release', $r->id) }}" class="inline">
                                                            @csrf
                                                            @method('patch')
                                                            <x-primary-button>Mark as Given</x-primary-button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-sm text-gray-500">No cash advances for release.</div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">Loan</div>
                                            <div class="text-xs text-gray-500">Approved but not yet given</div>
                                        </div>
                                        <div class="text-xs px-2 py-1 rounded-full bg-indigo-100 text-indigo-900 font-semibold">{{ (int) ($forRelease['counts']['loan'] ?? 0) }}</div>
                                    </div>

                                    <div class="mt-3 space-y-2 max-h-64 overflow-auto pr-1">
                                        @forelse (($forRelease['loan'] ?? []) as $r)
                                            <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div class="min-w-0">
                                                        <div class="truncate text-sm font-semibold text-gray-900">{{ $r->employee?->full_name }}</div>
                                                        <div class="text-xs text-gray-600">₱{{ number_format((float) $r->amount, 2) }}@if ($r->term_months) · {{ $r->term_months }}mo @endif</div>
                                                    </div>
                                                    <div class="shrink-0">
                                                        <form method="POST" action="{{ route('loan-requests.release', $r->id) }}" class="inline">
                                                            @csrf
                                                            @method('patch')
                                                            <x-primary-button>Mark as Given</x-primary-button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-sm text-gray-500">No loans for release.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if ($leaveRequests)
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Absence Requests') }}</h3>
                    <div class="mt-4 overflow-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">Employee</th>
                                    <th class="text-left py-2">Dates</th>
                                    <th class="text-left py-2">Type</th>
                                    <th class="text-left py-2">Duration</th>
                                    <th class="text-left py-2">Description</th>
                                    <th class="text-left py-2">Attachment</th>
                                    <th class="text-left py-2">Admin Notes</th>
                                    <th class="text-left py-2">Status</th>
                                    <th class="text-left py-2">Approved By</th>
                                    @if (auth()->user()?->canManageBackoffice())
                                        <th class="text-left py-2">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($leaveRequests as $r)
                                    <tr class="border-b">
                                        <td class="py-2">{{ $r->employee?->full_name }}</td>
                                        <td class="py-2">{{ optional($r->start_date)->format('Y-m-d') }} to {{ optional($r->end_date)->format('Y-m-d') }}</td>
                                        <td class="py-2">{{ $r->leave_type }}</td>
                                        <td class="py-2">{{ $r->duration_days }}</td>
                                        <td class="py-2">{{ $r->description }}</td>
                                        <td class="py-2">
                                            @if ($r->attachment_path)
                                                <a class="text-indigo-700 underline" href="{{ asset('storage/' . $r->attachment_path) }}" target="_blank">View</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-2">
                                            @if ($r->admin_notes)
                                                <span class="text-gray-700" title="{{ $r->admin_notes }}">{{ $r->admin_notes }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-2">
                                            @php
                                                $st = strtolower((string) $r->status);
                                                $badge = 'bg-gray-100 text-gray-700';
                                                if ($st === 'approved') {
                                                    $badge = 'bg-emerald-100 text-emerald-800';
                                                } elseif ($st === 'pending') {
                                                    $badge = 'bg-amber-100 text-amber-900';
                                                } elseif ($st === 'rejected') {
                                                    $badge = 'bg-red-100 text-red-800';
                                                }
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ ucfirst($st) }}</span>
                                        </td>
                                        <td class="py-2">{{ $r->approver?->full_name }}</td>
                                        @if (auth()->user()?->canManageBackoffice())
                                            <td class="py-2">
                                                @if ($r->status === 'pending')
                                                    <x-secondary-button type="button" x-data x-on:click="$dispatch('open-modal', 'approve-leave-{{ $r->id }}')">Approve</x-secondary-button>
                                                    <x-danger-button type="button" x-data x-on:click="$dispatch('open-modal', 'reject-leave-{{ $r->id }}')">Reject</x-danger-button>

                                                    <x-modal name="approve-leave-{{ $r->id }}" :show="false" focusable>
                                                        <form method="POST" action="{{ route('leave-requests.approve', $r->id) }}" class="p-6 space-y-4">
                                                            @csrf
                                                            @method('patch')

                                                            <div class="text-lg font-medium text-gray-900">Approve Absence Request</div>
                                                            <div class="text-sm text-gray-600">Optional: add notes for the employee.</div>

                                                            <div>
                                                                <x-input-label for="leave_admin_notes_approve_{{ $r->id }}" :value="__('Admin Notes (Optional)')" />
                                                                <textarea id="leave_admin_notes_approve_{{ $r->id }}" name="admin_notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                                            </div>

                                                            <div class="flex justify-end gap-2">
                                                                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'approve-leave-{{ $r->id }}')">Cancel</x-secondary-button>
                                                                <x-primary-button>Approve</x-primary-button>
                                                            </div>
                                                        </form>
                                                    </x-modal>

                                                    <x-modal name="reject-leave-{{ $r->id }}" :show="false" focusable>
                                                        <form method="POST" action="{{ route('leave-requests.reject', $r->id) }}" class="p-6 space-y-4">
                                                            @csrf
                                                            @method('patch')

                                                            <div class="text-lg font-medium text-gray-900">Reject Absence Request</div>
                                                            <div class="text-sm text-gray-600">Optional: add rejection reason/notes for the employee.</div>

                                                            <div>
                                                                <x-input-label for="leave_admin_notes_reject_{{ $r->id }}" :value="__('Admin Notes (Optional)')" />
                                                                <textarea id="leave_admin_notes_reject_{{ $r->id }}" name="admin_notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                                            </div>

                                                            <div class="flex justify-end gap-2">
                                                                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'reject-leave-{{ $r->id }}')">Cancel</x-secondary-button>
                                                                <x-danger-button>Reject</x-danger-button>
                                                            </div>
                                                        </form>
                                                    </x-modal>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $leaveRequests->links() }}</div>
                </div>
            @endif

            @if ($overtimeRequests)
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Overtime Requests') }}</h3>
                    <div class="mt-4 overflow-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">Employee</th>
                                    <th class="text-left py-2">Date</th>
                                    <th class="text-left py-2">Hours</th>
                                    <th class="text-left py-2">Description</th>
                                    <th class="text-left py-2">Reason</th>
                                    <th class="text-left py-2">Attachment</th>
                                    <th class="text-left py-2">Admin Notes</th>
                                    <th class="text-left py-2">Status</th>
                                    <th class="text-left py-2">Approved By</th>
                                    @if (auth()->user()?->canManageBackoffice())
                                        <th class="text-left py-2">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($overtimeRequests as $r)
                                    <tr class="border-b">
                                        <td class="py-2">{{ $r->employee?->full_name }}</td>
                                        <td class="py-2">{{ optional($r->date)->format('Y-m-d') }}</td>
                                        <td class="py-2">{{ $r->hours }}</td>
                                        <td class="py-2">{{ $r->description }}</td>
                                        <td class="py-2">{{ $r->reason }}</td>
                                        <td class="py-2">
                                            @if ($r->attachment_path)
                                                <a class="text-indigo-700 underline" href="{{ asset('storage/' . $r->attachment_path) }}" target="_blank">View</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-2">
                                            @if ($r->admin_notes)
                                                <span class="text-gray-700" title="{{ $r->admin_notes }}">{{ $r->admin_notes }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-2">
                                            @php
                                                $st = strtolower((string) $r->status);
                                                $badge = 'bg-gray-100 text-gray-700';
                                                if ($st === 'approved') {
                                                    $badge = 'bg-emerald-100 text-emerald-800';
                                                } elseif ($st === 'pending') {
                                                    $badge = 'bg-amber-100 text-amber-900';
                                                } elseif ($st === 'rejected') {
                                                    $badge = 'bg-red-100 text-red-800';
                                                }
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ ucfirst($st) }}</span>
                                        </td>
                                        <td class="py-2">{{ $r->approver?->full_name }}</td>
                                        @if (auth()->user()?->canManageBackoffice())
                                            <td class="py-2">
                                                @if ($r->status === 'pending')
                                                    <x-secondary-button type="button" x-data x-on:click="$dispatch('open-modal', 'approve-ot-{{ $r->id }}')">Approve</x-secondary-button>
                                                    <x-danger-button type="button" x-data x-on:click="$dispatch('open-modal', 'reject-ot-{{ $r->id }}')">Reject</x-danger-button>

                                                    <x-modal name="approve-ot-{{ $r->id }}" :show="false" focusable>
                                                        <form method="POST" action="{{ route('overtime-requests.approve', $r->id) }}" class="p-6 space-y-4">
                                                            @csrf
                                                            @method('patch')

                                                            <div class="text-lg font-medium text-gray-900">Approve Overtime Request</div>
                                                            <div class="text-sm text-gray-600">Optional: add notes for the employee.</div>

                                                            <div>
                                                                <x-input-label for="ot_admin_notes_approve_{{ $r->id }}" :value="__('Admin Notes (Optional)')" />
                                                                <textarea id="ot_admin_notes_approve_{{ $r->id }}" name="admin_notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                                            </div>

                                                            <div class="flex justify-end gap-2">
                                                                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'approve-ot-{{ $r->id }}')">Cancel</x-secondary-button>
                                                                <x-primary-button>Approve</x-primary-button>
                                                            </div>
                                                        </form>
                                                    </x-modal>

                                                    <x-modal name="reject-ot-{{ $r->id }}" :show="false" focusable>
                                                        <form method="POST" action="{{ route('overtime-requests.reject', $r->id) }}" class="p-6 space-y-4">
                                                            @csrf
                                                            @method('patch')

                                                            <div class="text-lg font-medium text-gray-900">Reject Overtime Request</div>
                                                            <div class="text-sm text-gray-600">Optional: add rejection reason/notes for the employee.</div>

                                                            <div>
                                                                <x-input-label for="ot_admin_notes_reject_{{ $r->id }}" :value="__('Admin Notes (Optional)')" />
                                                                <textarea id="ot_admin_notes_reject_{{ $r->id }}" name="admin_notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                                            </div>

                                                            <div class="flex justify-end gap-2">
                                                                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'reject-ot-{{ $r->id }}')">Cancel</x-secondary-button>
                                                                <x-danger-button>Reject</x-danger-button>
                                                            </div>
                                                        </form>
                                                    </x-modal>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $overtimeRequests->links() }}</div>
                </div>
            @endif

            @if ($lateRequests)
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Late / Undertime / Missed Logs Requests') }}</h3>
                    <div class="mt-4 overflow-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">Employee</th>
                                    <th class="text-left py-2">Date</th>
                                    <th class="text-left py-2">Type</th>
                                    <th class="text-left py-2">Minutes</th>
                                    <th class="text-left py-2">Reason</th>
                                    <th class="text-left py-2">Attachment</th>
                                    <th class="text-left py-2">Admin Notes</th>
                                    <th class="text-left py-2">Status</th>
                                    <th class="text-left py-2">Approved By</th>
                                    @if (auth()->user()?->canManageBackoffice())
                                        <th class="text-left py-2">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lateRequests as $r)
                                    <tr class="border-b">
                                        <td class="py-2">{{ $r->employee?->full_name }}</td>
                                        <td class="py-2">{{ optional($r->date)->format('Y-m-d') }}</td>
                                        <td class="py-2">{{ $r->type }}</td>
                                        <td class="py-2">{{ $r->minutes }}</td>
                                        <td class="py-2">{{ $r->reason }}</td>
                                        <td class="py-2">
                                            @if ($r->attachment_path)
                                                <a class="text-indigo-700 underline" href="{{ asset('storage/' . $r->attachment_path) }}" target="_blank">View</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-2">
                                            @if ($r->admin_notes)
                                                <span class="text-gray-700" title="{{ $r->admin_notes }}">{{ $r->admin_notes }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-2">
                                            @php
                                                $st = strtolower((string) $r->status);
                                                $badge = 'bg-gray-100 text-gray-700';
                                                if ($st === 'approved') {
                                                    $badge = 'bg-emerald-100 text-emerald-800';
                                                } elseif ($st === 'pending') {
                                                    $badge = 'bg-amber-100 text-amber-900';
                                                } elseif ($st === 'rejected') {
                                                    $badge = 'bg-red-100 text-red-800';
                                                }
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ ucfirst($st) }}</span>
                                        </td>
                                        <td class="py-2">{{ $r->approver?->full_name }}</td>
                                        @if (auth()->user()?->canManageBackoffice())
                                            <td class="py-2">
                                                @if ($r->status === 'pending')
                                                    <x-secondary-button type="button" x-data x-on:click="$dispatch('open-modal', 'approve-late-{{ $r->id }}')">Approve</x-secondary-button>
                                                    <x-danger-button type="button" x-data x-on:click="$dispatch('open-modal', 'reject-late-{{ $r->id }}')">Reject</x-danger-button>

                                                    <x-modal name="approve-late-{{ $r->id }}" :show="false" focusable>
                                                        <form method="POST" action="{{ route('late-requests.approve', $r->id) }}" class="p-6 space-y-4">
                                                            @csrf
                                                            @method('patch')

                                                            <div class="text-lg font-medium text-gray-900">Approve Late / Undertime / Missed Logs Request</div>
                                                            <div class="text-sm text-gray-600">Optional: add notes for the employee.</div>

                                                            <div>
                                                                <x-input-label for="late_admin_notes_approve_{{ $r->id }}" :value="__('Admin Notes (Optional)')" />
                                                                <textarea id="late_admin_notes_approve_{{ $r->id }}" name="admin_notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                                            </div>

                                                            <div class="flex justify-end gap-2">
                                                                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'approve-late-{{ $r->id }}')">Cancel</x-secondary-button>
                                                                <x-primary-button>Approve</x-primary-button>
                                                            </div>
                                                        </form>
                                                    </x-modal>

                                                    <x-modal name="reject-late-{{ $r->id }}" :show="false" focusable>
                                                        <form method="POST" action="{{ route('late-requests.reject', $r->id) }}" class="p-6 space-y-4">
                                                            @csrf
                                                            @method('patch')

                                                            <div class="text-lg font-medium text-gray-900">Reject Late / Undertime / Missed Logs Request</div>
                                                            <div class="text-sm text-gray-600">Optional: add rejection reason/notes for the employee.</div>

                                                            <div>
                                                                <x-input-label for="late_admin_notes_reject_{{ $r->id }}" :value="__('Admin Notes (Optional)')" />
                                                                <textarea id="late_admin_notes_reject_{{ $r->id }}" name="admin_notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                                            </div>

                                                            <div class="flex justify-end gap-2">
                                                                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'reject-late-{{ $r->id }}')">Cancel</x-secondary-button>
                                                                <x-danger-button>Reject</x-danger-button>
                                                            </div>
                                                        </form>
                                                    </x-modal>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $lateRequests->links() }}</div>
                </div>
            @endif

            @if (!empty($cashAdvanceRequests))
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Cash Advance Requests') }}</h3>
                    <div class="mt-4 overflow-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">Employee</th>
                                    <th class="text-left py-2">Amount</th>
                                    <th class="text-left py-2">Reason</th>
                                    <th class="text-left py-2">Details</th>
                                    <th class="text-left py-2">Attachment</th>
                                    <th class="text-left py-2">Admin Notes</th>
                                    <th class="text-left py-2">Status</th>
                                    <th class="text-left py-2">Approved By</th>
                                    <th class="text-left py-2">Released</th>
                                    <th class="text-left py-2">Deducted</th>
                                    @if (auth()->user()?->canManageBackoffice())
                                        <th class="text-left py-2">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cashAdvanceRequests as $r)
                                    <tr class="border-b">
                                        <td class="py-2">{{ $r->employee?->full_name }}</td>
                                        <td class="py-2">₱{{ number_format((float) $r->amount, 2) }}</td>
                                        <td class="py-2">{{ $r->reason }}</td>
                                        <td class="py-2">
                                            <a class="text-indigo-700 underline" href="{{ route('cash-advance-requests.show', $r->id) }}">View</a>
                                        </td>
                                        <td class="py-2">
                                            @if ($r->attachment_path)
                                                <a class="text-indigo-700 underline" href="{{ asset('storage/' . $r->attachment_path) }}" target="_blank">View</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-2">
                                            @if ($r->admin_notes)
                                                <span class="text-gray-700" title="{{ $r->admin_notes }}">{{ $r->admin_notes }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-2">
                                            @php
                                                $st = strtolower((string) $r->status);
                                                $badge = 'bg-gray-100 text-gray-700';
                                                if ($st === 'approved') {
                                                    $badge = 'bg-emerald-100 text-emerald-800';
                                                } elseif ($st === 'pending') {
                                                    $badge = 'bg-amber-100 text-amber-900';
                                                } elseif ($st === 'rejected') {
                                                    $badge = 'bg-red-100 text-red-800';
                                                }
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ ucfirst($st) }}</span>
                                        </td>
                                        <td class="py-2">{{ $r->approver?->full_name }}</td>
                                        <td class="py-2">
                                            @if ($r->released_at)
                                                {{ optional($r->released_at)->format('Y-m-d') }}
                                                @if ($r->releaser)
                                                    <div class="text-xs text-gray-600">{{ $r->releaser?->full_name }}</div>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-2">
                                            @if ($r->deducted_at)
                                                {{ optional($r->deducted_at)->format('Y-m-d') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        @if (auth()->user()?->canManageBackoffice())
                                            <td class="py-2">
                                                @if ($r->status === 'pending')
                                                    <x-secondary-button type="button" x-data x-on:click="$dispatch('open-modal', 'approve-cash-advance-table-{{ $r->id }}')">Approve</x-secondary-button>
                                                    <x-danger-button type="button" x-data x-on:click="$dispatch('open-modal', 'reject-cash-advance-table-{{ $r->id }}')">Reject</x-danger-button>

                                                @elseif ($r->status === 'approved' && !$r->released_at)
                                                    <form method="POST" action="{{ route('cash-advance-requests.release', $r->id) }}" class="inline">
                                                        @csrf
                                                        @method('patch')
                                                        <x-primary-button>Mark as Given</x-primary-button>
                                                    </form>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $cashAdvanceRequests->links() }}</div>
                </div>
            @endif

            @if (!empty($loanRequests))
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Loan Requests') }}</h3>
                    <div class="mt-4 overflow-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">Employee</th>
                                    <th class="text-left py-2">Amount</th>
                                    <th class="text-left py-2">Term</th>
                                    <th class="text-left py-2">Purpose</th>
                                    <th class="text-left py-2">Details</th>
                                    <th class="text-left py-2">Attachment</th>
                                    <th class="text-left py-2">Admin Notes</th>
                                    <th class="text-left py-2">Status</th>
                                    <th class="text-left py-2">Approved By</th>
                                    <th class="text-left py-2">Released</th>
                                    @if (auth()->user()?->canManageBackoffice())
                                        <th class="text-left py-2">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($loanRequests as $r)
                                    <tr class="border-b">
                                        <td class="py-2">{{ $r->employee?->full_name }}</td>
                                        <td class="py-2">₱{{ number_format((float) $r->amount, 2) }}</td>
                                        <td class="py-2">{{ $r->term_months ? ($r->term_months . ' months') : '-' }}</td>
                                        <td class="py-2">{{ $r->purpose }}</td>
                                        <td class="py-2">
                                            <a class="text-indigo-700 underline" href="{{ route('loan-requests.show', $r->id) }}">View</a>
                                        </td>
                                        <td class="py-2">
                                            @if ($r->attachment_path)
                                                <a class="text-indigo-700 underline" href="{{ asset('storage/' . $r->attachment_path) }}" target="_blank">View</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-2">
                                            @if ($r->admin_notes)
                                                <span class="text-gray-700" title="{{ $r->admin_notes }}">{{ $r->admin_notes }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-2">
                                            @php
                                                $st = strtolower((string) $r->status);
                                                $badge = 'bg-gray-100 text-gray-700';
                                                if ($st === 'approved') {
                                                    $badge = 'bg-emerald-100 text-emerald-800';
                                                } elseif ($st === 'pending') {
                                                    $badge = 'bg-amber-100 text-amber-900';
                                                } elseif ($st === 'rejected') {
                                                    $badge = 'bg-red-100 text-red-800';
                                                }
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ ucfirst($st) }}</span>
                                        </td>
                                        <td class="py-2">{{ $r->approver?->full_name }}</td>
                                        <td class="py-2">
                                            @if ($r->released_at)
                                                {{ optional($r->released_at)->format('Y-m-d') }}
                                                @if ($r->releaser)
                                                    <div class="text-xs text-gray-600">{{ $r->releaser?->full_name }}</div>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        @if (auth()->user()?->canManageBackoffice())
                                            <td class="py-2">
                                                @if ($r->status === 'pending')
                                                    <x-secondary-button type="button" x-data x-on:click="$dispatch('open-modal', 'approve-loan-table-{{ $r->id }}')">Approve</x-secondary-button>
                                                    <x-danger-button type="button" x-data x-on:click="$dispatch('open-modal', 'reject-loan-table-{{ $r->id }}')">Reject</x-danger-button>

                                                    <x-modal name="approve-loan-table-{{ $r->id }}" :show="false" focusable>
                                                        <form method="POST" action="{{ route('loan-requests.approve', $r->id) }}" class="p-6 space-y-4">
                                                            @csrf
                                                            @method('patch')

                                                            <div class="text-lg font-medium text-gray-900">Approve Loan Request</div>
                                                            <div class="text-sm text-gray-600">Optional: add notes for the employee.</div>

                                                            <div>
                                                                <x-input-label for="loan_admin_notes_approve_table_{{ $r->id }}" :value="__('Admin Notes (Optional)')" />
                                                                <textarea id="loan_admin_notes_approve_table_{{ $r->id }}" name="admin_notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                                            </div>

                                                            <div class="flex justify-end gap-2">
                                                                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'approve-loan-table-{{ $r->id }}')">Cancel</x-secondary-button>
                                                                <x-primary-button>Approve</x-primary-button>
                                                            </div>
                                                        </form>
                                                    </x-modal>

                                                    <x-modal name="reject-loan-table-{{ $r->id }}" :show="false" focusable>
                                                        <form method="POST" action="{{ route('loan-requests.reject', $r->id) }}" class="p-6 space-y-4">
                                                            @csrf
                                                            @method('patch')

                                                            <div class="text-lg font-medium text-gray-900">Reject Loan Request</div>
                                                            <div class="text-sm text-gray-600">Optional: add rejection reason/notes for the employee.</div>

                                                            <div>
                                                                <x-input-label for="loan_admin_notes_reject_table_{{ $r->id }}" :value="__('Admin Notes (Optional)')" />
                                                                <textarea id="loan_admin_notes_reject_table_{{ $r->id }}" name="admin_notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                                            </div>

                                                            <div class="flex justify-end gap-2">
                                                                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'reject-loan-table-{{ $r->id }}')">Cancel</x-secondary-button>
                                                                <x-danger-button>Reject</x-danger-button>
                                                            </div>
                                                        </form>
                                                    </x-modal>

                                                @elseif ($r->status === 'approved' && !$r->released_at)
                                                    <form method="POST" action="{{ route('loan-requests.release', $r->id) }}" class="inline">
                                                        @csrf
                                                        @method('patch')
                                                        <x-primary-button>Mark as Given</x-primary-button>
                                                    </form>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $loanRequests->links() }}</div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
