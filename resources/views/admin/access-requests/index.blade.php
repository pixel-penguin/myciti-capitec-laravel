<x-admin-layout>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Access Requests</h1>
            <p class="text-sm text-gray-500 mt-1">Review and manage employee access requests</p>
        </div>
    </div>

    {{-- Alert Banner --}}
    @if($pendingCount > 0)
        <div class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4 flex items-center gap-3">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span class="text-sm text-amber-800 font-medium">{{ $pendingCount }} pending {{ Str::plural('request', $pendingCount) }} require{{ $pendingCount === 1 ? 's' : '' }} attention</span>
        </div>
    @endif

    {{-- Tabs --}}
    <div class="border-b border-gray-200 mb-6">
        <nav class="flex gap-6">
            @foreach(['pending' => $pendingCount, 'approved' => $approvedCount, 'declined' => $declinedCount, 'all' => $allCount] as $tabName => $count)
                <a href="{{ route('admin.access-requests.index', ['tab' => $tabName]) }}"
                   class="pb-3 px-1 text-sm font-medium border-b-2 transition-colors {{ $tab === $tabName ? 'border-[#0077C8] text-[#0077C8]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    {{ ucfirst($tabName) }}
                    <span class="ml-1 px-2 py-0.5 rounded-full text-xs {{ $tab === $tabName ? 'bg-[#0077C8] text-white' : 'bg-gray-100 text-gray-600' }}">{{ $count }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Request Cards --}}
    <div class="space-y-4">
        @forelse($requests as $requestRow)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                    {{-- Avatar --}}
                    <div class="w-12 h-12 bg-[#0077C8] rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                        {{ strtoupper(substr($requestRow->name ?? $requestRow->email, 0, 1)) }}{{ strtoupper(substr(explode(' ', $requestRow->name ?? '')[1] ?? '', 0, 1)) }}
                    </div>

                    {{-- Details --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <h3 class="text-sm font-semibold text-gray-900">{{ $requestRow->name ?? 'Unknown' }}</h3>
                            @if($requestRow->status === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
                            @elseif($requestRow->status === 'approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Approved</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Declined</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 text-sm mb-3">
                            <div>
                                <span class="text-gray-500">Email:</span>
                                <span class="text-gray-900 ml-1">{{ $requestRow->email }}</span>
                            </div>
                            @if($requestRow->employee_id)
                                <div>
                                    <span class="text-gray-500">Employee ID:</span>
                                    <span class="text-gray-900 ml-1">{{ $requestRow->employee_id }}</span>
                                </div>
                            @endif
                            @if($requestRow->department)
                                <div>
                                    <span class="text-gray-500">Department:</span>
                                    <span class="text-gray-900 ml-1">{{ $requestRow->department }}</span>
                                </div>
                            @endif
                            <div>
                                <span class="text-gray-500">Requested:</span>
                                <span class="text-gray-900 ml-1">{{ optional($requestRow->requested_at)->format('M j, Y H:i') ?? $requestRow->created_at->format('M j, Y H:i') }}</span>
                            </div>
                        </div>

                        @if($requestRow->reason)
                            <div class="text-sm text-gray-600 bg-gray-50 rounded-lg p-3 mb-3">
                                <span class="font-medium">Reason:</span> {{ $requestRow->reason }}
                            </div>
                        @endif

                        @if($requestRow->review_notes)
                            <div class="text-sm text-gray-600 bg-red-50 rounded-lg p-3 mb-3">
                                <span class="font-medium">Review Notes:</span> {{ $requestRow->review_notes }}
                            </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    @if($requestRow->status === 'pending')
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <form method="POST" action="{{ route('admin.access-requests.approve', $requestRow) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Approve
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.access-requests.decline', $requestRow) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Decline
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-400">No {{ $tab === 'all' ? '' : $tab }} requests found</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $requests->links() }}
    </div>
</x-admin-layout>
