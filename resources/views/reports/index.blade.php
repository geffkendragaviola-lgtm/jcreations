<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Reports') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <div>
                        <div class="text-lg font-semibold">Attendance CSV</div>
                        <form method="GET" action="{{ route('reports.attendance.csv') }}" class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                            <div>
                                <x-input-label for="a_start" :value="__('Start')" />
                                <x-text-input id="a_start" name="start" type="date" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="a_end" :value="__('End')" />
                                <x-text-input id="a_end" name="end" type="date" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-primary-button>Download</x-primary-button>
                            </div>
                        </form>
                    </div>

                    <div class="border-t pt-6">
                        <div class="text-lg font-semibold">Leaves CSV</div>
                        <form method="GET" action="{{ route('reports.leaves.csv') }}" class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                            <div>
                                <x-input-label for="l_start" :value="__('Start')" />
                                <x-text-input id="l_start" name="start" type="date" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="l_end" :value="__('End')" />
                                <x-text-input id="l_end" name="end" type="date" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-primary-button>Download</x-primary-button>
                            </div>
                        </form>
                    </div>

                    <div class="border-t pt-6">
                        <div class="text-lg font-semibold">Payroll CSV</div>
                        <div class="text-sm text-gray-600">Provide a Payroll Run ID.</div>
                        <form method="GET" action="{{ route('reports.payroll.csv') }}" class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                            <div>
                                <x-input-label for="payroll_run_id" :value="__('Payroll Run ID')" />
                                <x-text-input id="payroll_run_id" name="payroll_run_id" type="number" min="1" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-primary-button>Download</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
