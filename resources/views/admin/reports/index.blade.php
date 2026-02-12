<x-admin-layout>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Reports & Analytics</h1>
            <p class="text-sm text-gray-500 mt-1">Shuttle usage data and performance metrics</p>
        </div>
        <div class="flex items-center gap-2 mt-3 sm:mt-0">
            <form method="POST" action="{{ route('admin.reports.export') }}" class="inline">
                @csrf
                <input type="hidden" name="type" value="validation">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-[#0077C8] hover:bg-[#005A9E] text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export Report (CSV)
                </button>
            </form>
        </div>
    </div>

    {{-- Report Viewer Toggle --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6" x-data="{ viewer: 'capitec' }">
        <div class="flex items-center gap-4">
            <span class="text-sm font-medium text-gray-700">Report Viewer:</span>
            <div class="inline-flex rounded-lg bg-gray-100 p-1">
                <button @click="viewer = 'capitec'"
                        :class="viewer === 'capitec' ? 'bg-[#0077C8] text-white shadow-sm' : 'text-gray-600 hover:text-gray-800'"
                        class="px-4 py-1.5 text-sm font-medium rounded-md transition-colors">
                    Capitec Internal
                </button>
                <button @click="viewer = 'city'"
                        :class="viewer === 'city' ? 'bg-[#0077C8] text-white shadow-sm' : 'text-gray-600 hover:text-gray-800'"
                        class="px-4 py-1.5 text-sm font-medium rounded-md transition-colors">
                    City of Cape Town
                </button>
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    @php
        $totalEvents = $statusCounts->sum();
        $passCount = $statusCounts['pass'] ?? 0;
        $declineCount = $statusCounts['decline'] ?? 0;
        $trackingTotal = $trackingCounts->sum();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Rides</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalEvents) }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#0077C8]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Success Rate</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $passRate }}%</p>
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
                    <p class="text-sm text-gray-500">Declined Scans</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($declineCount) }}</p>
                </div>
                <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Tracking Views</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1">{{ number_format($trackingTotal) }}</p>
                </div>
                <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Shuttle Usage Per Schedule --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900">Shuttle Usage Per Schedule</h3>
                <form method="POST" action="{{ route('admin.reports.export') }}" class="inline">
                    @csrf
                    <input type="hidden" name="type" value="validation">
                    <button type="submit" class="text-xs text-[#0077C8] hover:text-[#005A9E] font-medium">Export</button>
                </form>
            </div>
            <div class="space-y-3">
                @foreach ($schedules as $schedule)
                    @php $count = $scheduleCounts[$schedule->id] ?? 0; $max = $scheduleCounts->max() ?: 1; @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">{{ $schedule->name }}</span>
                            <span class="font-medium text-gray-900">{{ number_format($count) }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-[#0077C8] h-2 rounded-full" style="width: {{ ($count / $max) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Success vs Declined Scans --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900">Validation Event Types</h3>
            </div>
            <div class="space-y-3">
                @foreach ($statusCounts as $type => $total)
                    @php $max = $statusCounts->max() ?: 1; @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600 capitalize">{{ $type }}</span>
                            <span class="font-medium text-gray-900">{{ number_format($total) }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="{{ $type === 'pass' ? 'bg-green-500' : ($type === 'decline' ? 'bg-red-500' : 'bg-amber-500') }} h-2 rounded-full" style="width: {{ ($total / $max) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
                <div class="mt-3 pt-3 border-t border-gray-100 flex gap-4 text-sm">
                    <span class="text-gray-500">Pass rate: <span class="font-semibold text-green-600">{{ $passRate }}%</span></span>
                    <span class="text-gray-500">Decline rate: <span class="font-semibold text-red-600">{{ $declineRate }}%</span></span>
                </div>
            </div>
        </div>

        {{-- Tracking Screen Engagement --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900">Tracking Engagement by Schedule</h3>
                <form method="POST" action="{{ route('admin.reports.export') }}" class="inline">
                    @csrf
                    <input type="hidden" name="type" value="tracking">
                    <button type="submit" class="text-xs text-[#0077C8] hover:text-[#005A9E] font-medium">Export</button>
                </form>
            </div>
            <div class="space-y-3">
                @foreach ($schedules as $schedule)
                    @php $count = $trackingCounts[$schedule->id] ?? 0; $max = $trackingCounts->max() ?: 1; @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">{{ $schedule->name }}</span>
                            <span class="font-medium text-gray-900">{{ number_format($count) }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-purple-500 h-2 rounded-full" style="width: {{ ($count / $max) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Usage by Bus --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Usage by Bus</h3>
            <div class="space-y-3">
                @forelse ($busCounts as $busId => $total)
                    @php $max = $busCounts->max() ?: 1; @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Bus {{ $busId }}</span>
                            <span class="font-medium text-gray-900">{{ number_format($total) }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-amber-500 h-2 rounded-full" style="width: {{ ($total / $max) * 100 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">No bus usage data</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Daily Usage Table --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Daily Usage (Last 14 Days)</h3>
            <div class="space-y-2">
                @forelse ($dailyUsage as $row)
                    <div class="flex justify-between items-center py-1.5 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                        <span class="text-sm text-gray-600">{{ $row->day }}</span>
                        <div class="flex items-center gap-2">
                            <div class="w-24 bg-gray-100 rounded-full h-1.5">
                                <div class="bg-[#0077C8] h-1.5 rounded-full" style="width: {{ $dailyUsage->max('total') > 0 ? ($row->total / $dailyUsage->max('total')) * 100 : 0 }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 w-8 text-right">{{ $row->total }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">No daily data</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Peak Usage Times (Hourly)</h3>
            <div class="space-y-2">
                @forelse ($hourlyUsage as $row)
                    <div class="flex justify-between items-center py-1.5 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                        <span class="text-sm text-gray-600">{{ str_pad($row->hour, 2, '0', STR_PAD_LEFT) }}:00</span>
                        <div class="flex items-center gap-2">
                            <div class="w-24 bg-gray-100 rounded-full h-1.5">
                                <div class="bg-purple-500 h-1.5 rounded-full" style="width: {{ $hourlyUsage->max('total') > 0 ? ($row->total / $hourlyUsage->max('total')) * 100 : 0 }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 w-8 text-right">{{ $row->total }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">No hourly data</p>
                @endforelse
            </div>
        </div>
    </div>
</x-admin-layout>
