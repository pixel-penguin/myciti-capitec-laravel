<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reporting Dashboard
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Usage by Schedule</h3>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="py-2">Schedule</th>
                            <th class="py-2">Total Scans</th>
                            <th class="py-2">Tracking Opens</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($schedules as $schedule)
                            <tr class="border-t">
                                <td class="py-2">{{ $schedule->name }}</td>
                                <td class="py-2">{{ $scheduleCounts[$schedule->id] ?? 0 }}</td>
                                <td class="py-2">{{ $trackingCounts[$schedule->id] ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Validator Event Types</h3>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500">
                                <th class="py-2">Type</th>
                                <th class="py-2">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($statusCounts as $type => $total)
                                <tr class="border-t">
                                    <td class="py-2">{{ $type }}</td>
                                    <td class="py-2">{{ $total }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Usage by Bus</h3>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500">
                                <th class="py-2">Bus ID</th>
                                <th class="py-2">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($busCounts as $busId => $total)
                                <tr class="border-t">
                                    <td class="py-2">{{ $busId }}</td>
                                    <td class="py-2">{{ $total }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Export Reports</h3>
                <div class="flex flex-wrap gap-4">
                    <form method="POST" action="{{ route('admin.reports.export') }}">
                        @csrf
                        <input type="hidden" name="type" value="validation">
                        <button class="px-4 py-2 bg-slate-900 text-white rounded">Export Validation CSV</button>
                    </form>
                    <form method="POST" action="{{ route('admin.reports.export') }}">
                        @csrf
                        <input type="hidden" name="type" value="tracking">
                        <button class="px-4 py-2 bg-slate-900 text-white rounded">Export Tracking CSV</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
