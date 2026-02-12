<x-admin-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded bg-green-50 p-4 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Existing Schedules</h3>
                    <a href="{{ route('admin.schedules.create') }}" class="px-4 py-2 bg-slate-900 text-white rounded">Create Schedule</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500">
                                <th class="py-2">Name</th>
                                <th class="py-2">Start</th>
                                <th class="py-2">End</th>
                                <th class="py-2">Active</th>
                                <th class="py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($schedules as $schedule)
                                <tr class="border-t">
                                    <td class="py-2">{{ $schedule->name }}</td>
                                    <td class="py-2">{{ $schedule->starts_at }}</td>
                                    <td class="py-2">{{ $schedule->ends_at }}</td>
                                    <td class="py-2">{{ $schedule->active ? 'Yes' : 'No' }}</td>
                                    <td class="py-2">
                                        <a href="{{ route('admin.schedules.edit', $schedule) }}" class="px-3 py-1 bg-slate-800 text-white rounded">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
