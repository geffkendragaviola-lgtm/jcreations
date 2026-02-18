<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Create Employee') }}</h2>
            <a href="{{ route('employees.index') }}" class="text-sm text-indigo-700 underline">Back to Employees</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    @if ($errors->any())
                        <div class="p-3 rounded bg-red-50 text-red-700 border border-red-200">
                            <div class="font-semibold">Please fix the errors and try again.</div>
                            <ul class="list-disc ml-6">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('employees.store') }}" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="employee_code" :value="__('Employee Code')" />
                                <x-text-input id="employee_code" name="employee_code" type="text" class="mt-1 block w-full" :value="old('employee_code')" required />
                                <x-input-error :messages="$errors->get('employee_code')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="employment_status" :value="__('Employment Status')" />
                                <select id="employment_status" name="employment_status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    @php $st = old('employment_status', 'active'); @endphp
                                    <option value="active" {{ $st === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="probation" {{ $st === 'probation' ? 'selected' : '' }}>Probation</option>
                                    <option value="on_leave" {{ $st === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                    <option value="resigned" {{ $st === 'resigned' ? 'selected' : '' }}>Resigned</option>
                                    <option value="terminated" {{ $st === 'terminated' ? 'selected' : '' }}>Terminated</option>
                                </select>
                                <x-input-error :messages="$errors->get('employment_status')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="first_name" :value="__('First Name')" />
                                <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name')" required />
                                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="middle_name" :value="__('Middle Name')" />
                                <x-text-input id="middle_name" name="middle_name" type="text" class="mt-1 block w-full" :value="old('middle_name')" />
                                <x-input-error :messages="$errors->get('middle_name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="last_name" :value="__('Last Name')" />
                                <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name')" required />
                                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Email (Optional)')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="phone" :value="__('Phone (Optional)')" />
                                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone')" />
                                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="department_id" :value="__('Department (Optional)')" />
                                <select id="department_id" name="department_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">-</option>
                                    @foreach ($departments as $d)
                                        <option value="{{ $d->id }}" {{ (string) old('department_id') === (string) $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('department_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="position" :value="__('Position (Optional)')" />
                                <x-text-input id="position" name="position" type="text" class="mt-1 block w-full" :value="old('position')" />
                                <x-input-error :messages="$errors->get('position')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="contract_type" :value="__('Contract Type (Optional)')" />
                                <select id="contract_type" name="contract_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">-</option>
                                    @foreach (['Permanent','Temporary','Seasonal','Full-Time','Part-Time','Rank and File','Executive'] as $t)
                                        <option value="{{ $t }}" {{ (string) old('contract_type') === (string) $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('contract_type')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="contract_start_date" :value="__('Contract Start Date (Optional)')" />
                                <x-text-input id="contract_start_date" name="contract_start_date" type="date" class="mt-1 block w-full" :value="old('contract_start_date')" />
                                <x-input-error :messages="$errors->get('contract_start_date')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <x-secondary-button type="button" onclick="window.location='{{ route('employees.index') }}'">Cancel</x-secondary-button>
                            <x-primary-button>Create</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
