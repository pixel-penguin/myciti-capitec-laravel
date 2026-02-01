<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Admin Audit Logs
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Recent Actions</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500">
                                <th class="py-2">Time</th>
                                <th class="py-2">Actor</th>
                                <th class="py-2">Action</th>
                                <th class="py-2">Target</th>
                                <th class="py-2">IP</th>
                                <th class="py-2">Metadata</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr class="border-t">
                                    <td class="py-2">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                                    <td class="py-2">{{ $log->actor?->name ?? 'System' }}</td>
                                    <td class="py-2">{{ $log->action }}</td>
                                    <td class="py-2">
                                        @if ($log->target_type)
                                            {{ class_basename($log->target_type) }} #{{ $log->target_id ?? '—' }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="py-2">{{ $log->ip_address ?? '—' }}</td>
                                    <td class="py-2">
                                        @if ($log->metadata)
                                            <span class="text-xs text-slate-600">{{ json_encode($log->metadata) }}</span>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr class="border-t">
                                    <td class="py-4 text-slate-500" colspan="6">No audit entries yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
