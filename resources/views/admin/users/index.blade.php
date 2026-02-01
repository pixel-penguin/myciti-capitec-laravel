<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            User Management
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
                <h3 class="text-lg font-semibold mb-4">Registered Users</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500">
                                <th class="py-2">Name</th>
                                <th class="py-2">Email</th>
                                <th class="py-2">Roles</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">2FA</th>
                                <th class="py-2">Last Login</th>
                                <th class="py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr class="border-t">
                                    <td class="py-2">{{ $user->name }}</td>
                                    <td class="py-2">{{ $user->email }}</td>
                                    <td class="py-2">
                                        @if ($user->roles->isEmpty())
                                            <span class="text-slate-400">—</span>
                                        @else
                                            {{ $user->roles->pluck('name')->implode(', ') }}
                                        @endif
                                    </td>
                                    <td class="py-2">{{ $user->status ?? 'active' }}</td>
                                    <td class="py-2">{{ $user->two_factor_enabled ? 'Enabled' : 'Off' }}</td>
                                    <td class="py-2">{{ optional($user->last_login_at)->format('Y-m-d H:i') ?? '—' }}</td>
                                    <td class="py-2">
                                        <form method="POST" action="{{ route('admin.users.update-status', $user) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="border rounded px-2 py-1">
                                                <option value="active" @selected(($user->status ?? 'active') === 'active')>Active</option>
                                                <option value="suspended" @selected(($user->status ?? '') === 'suspended')>Suspended</option>
                                            </select>
                                            <button class="px-3 py-1 bg-slate-800 text-white rounded">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr class="border-t">
                                    <td class="py-4 text-slate-500" colspan="7">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
