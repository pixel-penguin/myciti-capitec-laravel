<x-admin-layout>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notice Management</h1>
            <p class="text-sm text-gray-500 mt-1">Manage public notices for Capitec employees</p>
        </div>
        <a href="{{ route('admin.service-messages.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#0077C8] hover:bg-[#005A9E] text-white text-sm font-medium rounded-lg transition-colors mt-3 sm:mt-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Create Notice
        </a>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active Notices</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $activeCount }}</p>
                </div>
                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Scheduled</p>
                    <p class="text-2xl font-bold text-[#0077C8] mt-1">{{ $scheduledCount }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#0077C8]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Expired</p>
                    <p class="text-2xl font-bold text-gray-500 mt-1">{{ $expiredCount }}</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Notice Cards --}}
    <div class="space-y-4">
        @forelse ($messages as $message)
            @php
                $now = now();
                $isActive = $message->is_active
                    && ($message->starts_at === null || $message->starts_at <= $now)
                    && ($message->ends_at === null || $message->ends_at >= $now);
                $isScheduled = $message->is_active && $message->starts_at && $message->starts_at > $now;
                $isExpired = !$message->is_active || ($message->ends_at && $message->ends_at < $now);
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex flex-col sm:flex-row gap-4">
                    {{-- Image thumbnail --}}
                    @if($message->image_path)
                        <div class="flex-shrink-0">
                            <img src="{{ \App\Http\Controllers\Admin\ServiceMessageController::imageUrl($message->image_path) }}" alt="{{ $message->title }}" class="w-36 h-24 object-cover rounded-lg border border-gray-200">
                        </div>
                    @endif

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h3 class="text-base font-semibold text-gray-900">{{ $message->title }}</h3>
                            @if($isScheduled)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Scheduled</span>
                            @elseif($isActive)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Expired</span>
                            @endif
                        </div>

                        @if($message->body)
                            <p class="text-sm text-gray-600 mb-2">{{ Str::limit($message->body, 200) }}</p>
                        @endif

                        <div class="flex flex-wrap gap-4 text-xs text-gray-500">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                Start: {{ $message->starts_at?->format('d/m/Y') ?? 'No start date' }}
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                End: {{ $message->ends_at?->format('d/m/Y') ?? 'No end date' }}
                            </span>
                            @if($message->creator)
                                <span>Created by {{ $message->creator->name }} on {{ $message->created_at?->format('d/m/Y') }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-start gap-2 flex-shrink-0">
                        <a href="{{ route('admin.service-messages.edit', $message) }}" class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition-colors" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </a>
                        <form method="POST" action="{{ route('admin.service-messages.destroy', $message) }}" onsubmit="return confirm('Are you sure you want to delete this notice?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition-colors" title="Delete">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <p class="text-gray-400">No notices yet. Create your first notice.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $messages->links() }}
    </div>
</x-admin-layout>
