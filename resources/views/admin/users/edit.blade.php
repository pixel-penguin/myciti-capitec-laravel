<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit User
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Name</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="border rounded px-3 py-2 w-full" required>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="border rounded px-3 py-2 w-full" required>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="border rounded px-3 py-2 w-full">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Status</label>
                            <select name="status" class="border rounded px-3 py-2 w-full">
                                <option value="active" @selected(old('status', $user->status) === 'active')>Active</option>
                                <option value="suspended" @selected(old('status', $user->status) === 'suspended')>Suspended</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">New Password</label>
                            <input type="password" name="password" class="border rounded px-3 py-2 w-full" placeholder="Leave blank to keep">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="border rounded px-3 py-2 w-full">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-600 mb-2">Roles</label>
                        <div class="flex flex-wrap gap-4">
                            @foreach ($roles as $role)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" @checked(in_array($role->name, old('roles', $user->roles->pluck('name')->all()), true))>
                                    {{ $role->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" name="two_factor_enabled" value="1" @checked(old('two_factor_enabled', $user->two_factor_enabled))>
                            Require 2FA for this user
                        </label>
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
                        <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-600">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
