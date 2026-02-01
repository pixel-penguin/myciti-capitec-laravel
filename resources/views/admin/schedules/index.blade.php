<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Schedules
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded bg-green-50 p-4 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Add Schedule</h3>
                <form method="POST" action="{{ route('admin.schedules.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    @csrf
                    <input type="text" name="name" placeholder="Name (morning_peak)" class="border rounded px-3 py-2" required>
                    <input type="time" name="starts_at" class="border rounded px-3 py-2" required>
                    <input type="time" name="ends_at" class="border rounded px-3 py-2" required>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="active" value="1" checked>
                        Active
                    </label>
                    <button class="md:col-span-5 px-4 py-2 bg-slate-900 text-white rounded">Create</button>
                </form>
            </div>

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Existing Schedules</h3>
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
                                        <form method="POST" action="{{ route('admin.schedules.update', $schedule) }}" class="flex flex-wrap items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="text" name="name" value="{{ $schedule->name }}" class="border rounded px-2 py-1" required>
                                            <input type="time" name="starts_at" value="{{ $schedule->starts_at }}" class="border rounded px-2 py-1" required>
                                            <input type="time" name="ends_at" value="{{ $schedule->ends_at }}" class="border rounded px-2 py-1" required>
                                            <label class="flex items-center gap-2 text-xs">
                                                <input type="checkbox" name="active" value="1" @checked($schedule->active)>
                                                Active
                                            </label>
                                            <button class="px-3 py-1 bg-slate-800 text-white rounded">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
