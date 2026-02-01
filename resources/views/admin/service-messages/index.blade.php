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
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Existing Messages</h3>
                    <a href="{{ route('admin.service-messages.create') }}" class="px-4 py-2 bg-slate-900 text-white rounded">Create Message</a>
                </div>
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
                                        <a href="{{ route('admin.service-messages.edit', $message) }}" class="px-3 py-1 bg-slate-800 text-white rounded">Edit</a>
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
