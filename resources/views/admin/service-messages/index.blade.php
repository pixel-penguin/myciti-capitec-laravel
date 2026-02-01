<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Service Messages
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
                <h3 class="text-lg font-semibold mb-4">Create Message</h3>
                <form method="POST" action="{{ route('admin.service-messages.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    @csrf
                    <input type="text" name="title" placeholder="Title" class="border rounded px-3 py-2 md:col-span-2" required>
                    <input type="text" name="body" placeholder="Message" class="border rounded px-3 py-2 md:col-span-2">
                    <select name="level" class="border rounded px-3 py-2">
                        <option value="info">Info</option>
                        <option value="warning">Warning</option>
                        <option value="alert">Alert</option>
                    </select>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" checked>
                        Active
                    </label>
                    <input type="datetime-local" name="starts_at" class="border rounded px-3 py-2 md:col-span-2">
                    <input type="datetime-local" name="ends_at" class="border rounded px-3 py-2 md:col-span-2">
                    <button class="md:col-span-6 px-4 py-2 bg-slate-900 text-white rounded">Publish</button>
                </form>
                <p class="mt-2 text-sm text-slate-500">Leave start/end empty for immediate and indefinite messages.</p>
            </div>

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Existing Messages</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500">
                                <th class="py-2">Title</th>
                                <th class="py-2">Level</th>
                                <th class="py-2">Active</th>
                                <th class="py-2">Window</th>
                                <th class="py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($messages as $message)
                                <tr class="border-t">
                                    <td class="py-2">
                                        <div class="font-medium">{{ $message->title }}</div>
                                        <div class="text-xs text-slate-500">{{ $message->body }}</div>
                                    </td>
                                    <td class="py-2">{{ ucfirst($message->level) }}</td>
                                    <td class="py-2">{{ $message->is_active ? 'Yes' : 'No' }}</td>
                                    <td class="py-2 text-xs text-slate-500">
                                        {{ $message->starts_at?->format('Y-m-d H:i') ?? '—' }}
                                        <span class="mx-1">→</span>
                                        {{ $message->ends_at?->format('Y-m-d H:i') ?? '—' }}
                                    </td>
                                    <td class="py-2">
                                        <form method="POST" action="{{ route('admin.service-messages.update', $message) }}" class="grid grid-cols-1 md:grid-cols-7 gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="text" name="title" value="{{ $message->title }}" class="border rounded px-2 py-1" required>
                                            <input type="text" name="body" value="{{ $message->body }}" class="border rounded px-2 py-1">
                                            <select name="level" class="border rounded px-2 py-1">
                                                <option value="info" @selected($message->level === 'info')>Info</option>
                                                <option value="warning" @selected($message->level === 'warning')>Warning</option>
                                                <option value="alert" @selected($message->level === 'alert')>Alert</option>
                                            </select>
                                            <input type="datetime-local" name="starts_at" value="{{ $message->starts_at?->format('Y-m-d\\TH:i') }}" class="border rounded px-2 py-1">
                                            <input type="datetime-local" name="ends_at" value="{{ $message->ends_at?->format('Y-m-d\\TH:i') }}" class="border rounded px-2 py-1">
                                            <select name="is_active" class="border rounded px-2 py-1">
                                                <option value="1" @selected($message->is_active)>Active</option>
                                                <option value="0" @selected(! $message->is_active)>Inactive</option>
                                            </select>
                                            <button class="px-3 py-1 bg-slate-800 text-white rounded">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr class="border-t">
                                    <td class="py-4 text-slate-500" colspan="5">No service messages yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $messages->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
