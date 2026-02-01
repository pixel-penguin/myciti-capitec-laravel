<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Employee Eligibility
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.eligibility.update', $employee) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email', $employee->email) }}" class="border rounded px-3 py-2 w-full" required>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Status</label>
                            <select name="status" class="border rounded px-3 py-2 w-full">
                                <option value="active" @selected(old('status', $employee->status) === 'active')>Active</option>
                                <option value="suspended" @selected(old('status', $employee->status) === 'suspended')>Suspended</option>
                                <option value="left_company" @selected(old('status', $employee->status) === 'left_company')>Left company</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">First name</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}" class="border rounded px-3 py-2 w-full">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Last name</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}" class="border rounded px-3 py-2 w-full">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="border rounded px-3 py-2 w-full">
                        </div>
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
                        <a href="{{ route('admin.eligibility.index') }}" class="text-sm text-slate-600">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
