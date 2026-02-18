<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit User') }}</h2>
            <a href="{{ route('users.index') }}" class="text-sm text-indigo-700 underline">Back to Users</a>
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

                    <form method="POST" action="{{ route('users.update', $editUser) }}" class="space-y-4">
                        @csrf
                        @method('patch')

                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $editUser->name)" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $editUser->email)" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="employee_id" :value="__('Link Employee (Optional)')" />
                            <select id="employee_id" name="employee_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-</option>
                                @foreach ($employees as $e)
                                    <option value="{{ $e->id }}" {{ (string) old('employee_id', $editUser->employee_id) === (string) $e->id ? 'selected' : '' }}>
                                        {{ $e->employee_code }} - {{ $e->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('employee_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label :value="__('Roles (Employee roles)')" />
                            @php
                                $selectedRoles = old('roles', $editUser->employee ? $editUser->employee->roles->pluck('id')->all() : []);
                            @endphp
                            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach ($roles as $role)
                                    <label class="inline-flex items-center gap-2">
                                        <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="rounded border-gray-300" {{ in_array($role->id, $selectedRoles) ? 'checked' : '' }}>
                                        <span class="text-sm text-gray-700">{{ $role->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('roles')" class="mt-2" />
                        </div>

                        <div class="border-t pt-4">
                            <div class="text-sm font-semibold text-gray-900">Change Password (Optional)</div>
                            <div class="text-sm text-gray-600">Leave blank to keep current password.</div>

                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="password" :value="__('New Password')" />
                                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" />
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <x-secondary-button type="button" onclick="window.location='{{ route('users.index') }}'">Cancel</x-secondary-button>
                            <x-primary-button>Save</x-primary-button>
                        </div>
                    </form>

                    <div class="border-t pt-4">
                        <form method="POST" action="{{ route('users.destroy', $editUser) }}" onsubmit="return confirm('Delete this user?');">
                            @csrf
                            @method('delete')
                            <x-danger-button>Delete User</x-danger-button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
