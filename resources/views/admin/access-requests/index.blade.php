<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Access Requests
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
                <h3 class="text-lg font-semibold mb-4">Requests</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500">
                                <th class="py-2">Email</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Requested</th>
                                <th class="py-2">Reviewed</th>
                                <th class="py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $requestRow)
                                <tr class="border-t">
                                    <td class="py-2">{{ $requestRow->email }}</td>
                                    <td class="py-2">{{ $requestRow->status }}</td>
                                    <td class="py-2">{{ optional($requestRow->requested_at)->format('Y-m-d H:i') }}</td>
                                    <td class="py-2">{{ optional($requestRow->reviewed_at)->format('Y-m-d H:i') }}</td>
                                    <td class="py-2">
                                        @if ($requestRow->status === 'pending')
                                            <div class="flex flex-wrap gap-2">
                                                <form method="POST" action="{{ route('admin.access-requests.approve', $requestRow) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="px-3 py-1 bg-emerald-600 text-white rounded">Approve</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.access-requests.decline', $requestRow) }}" class="flex items-center gap-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="text" name="review_notes" placeholder="Reason" class="border rounded px-2 py-1">
                                                    <button class="px-3 py-1 bg-rose-600 text-white rounded">Decline</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-slate-500">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
