<x-admin-layout>
    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.schedules.update', $schedule) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Name</label>
                            <input type="text" name="name" value="{{ old('name', $schedule->name) }}" class="border rounded px-3 py-2 w-full" required>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Active</label>
                            <select name="active" class="border rounded px-3 py-2 w-full">
                                <option value="1" @selected(old('active', $schedule->active ? '1' : '0') === '1')>Yes</option>
                                <option value="0" @selected(old('active', $schedule->active ? '1' : '0') === '0')>No</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Starts at</label>
                            <input type="time" name="starts_at" value="{{ old('starts_at', $schedule->starts_at) }}" class="border rounded px-3 py-2 w-full" required>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Ends at</label>
                            <input type="time" name="ends_at" value="{{ old('ends_at', $schedule->ends_at) }}" class="border rounded px-3 py-2 w-full" required>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="rounded bg-rose-50 p-3 text-rose-700 text-sm">
                            <ul class="list-disc ml-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="flex items-center gap-3">
                        <button class="px-4 py-2 bg-slate-900 text-white rounded">Save Changes</button>
                        <a href="{{ route('admin.schedules.index') }}" class="text-sm text-slate-600">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
