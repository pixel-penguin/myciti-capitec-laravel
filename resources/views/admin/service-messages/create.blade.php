<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create Service Message
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.service-messages.store') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Title</label>
                            <input type="text" name="title" value="{{ old('title') }}" class="border rounded px-3 py-2 w-full" required>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Level</label>
                            <select name="level" class="border rounded px-3 py-2 w-full">
                                <option value="info" @selected(old('level', 'info') === 'info')>Info</option>
                                <option value="warning" @selected(old('level') === 'warning')>Warning</option>
                                <option value="alert" @selected(old('level') === 'alert')>Alert</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm text-slate-600 mb-1">Message</label>
                            <textarea name="body" rows="3" class="border rounded px-3 py-2 w-full">{{ old('body') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Starts at</label>
                            <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="border rounded px-3 py-2 w-full">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Ends at</label>
                            <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" class="border rounded px-3 py-2 w-full">
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                        Active
                    </label>

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
                        <button class="px-4 py-2 bg-slate-900 text-white rounded">Publish</button>
                        <a href="{{ route('admin.service-messages.index') }}" class="text-sm text-slate-600">Cancel</a>
                    </div>
                </form>
                <p class="mt-2 text-sm text-slate-500">Leave start/end empty for immediate and indefinite messages.</p>
            </div>
        </div>
    </div>
</x-app-layout>
