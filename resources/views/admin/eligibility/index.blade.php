<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Employee Eligibility
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
                <h3 class="text-lg font-semibold mb-4">Upload CSV</h3>
                <form method="POST" action="{{ route('admin.eligibility.upload') }}" enctype="multipart/form-data" class="flex flex-col md:flex-row md:items-center gap-4">
                    @csrf
                    <input type="file" name="file" class="block w-full" required>
                    <button class="px-4 py-2 bg-slate-900 text-white rounded">Upload</button>
                </form>
                <p class="mt-2 text-sm text-slate-500">Columns: email, first_name, last_name, phone, status.</p>
            </div>

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Add / Update Employee</h3>
                <form method="POST" action="{{ route('admin.eligibility.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    @csrf
                    <input type="email" name="email" placeholder="Email" class="border rounded px-3 py-2" required>
                    <input type="text" name="first_name" placeholder="First name" class="border rounded px-3 py-2">
                    <input type="text" name="last_name" placeholder="Last name" class="border rounded px-3 py-2">
                    <input type="text" name="phone" placeholder="Phone" class="border rounded px-3 py-2">
                    <select name="status" class="border rounded px-3 py-2">
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="left_company">Left company</option>
                    </select>
                    <button class="md:col-span-5 px-4 py-2 bg-slate-900 text-white rounded">Save</button>
                </form>
            </div>

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Eligibility List</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500">
                                <th class="py-2">Email</th>
                                <th class="py-2">Name</th>
                                <th class="py-2">Phone</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $employee)
                                <tr class="border-t">
                                    <td class="py-2">{{ $employee->email }}</td>
                                    <td class="py-2">{{ $employee->first_name }} {{ $employee->last_name }}</td>
                                    <td class="py-2">{{ $employee->phone }}</td>
                                    <td class="py-2">{{ $employee->status }}</td>
                                    <td class="py-2">
                                        <form method="POST" action="{{ route('admin.eligibility.update-status', $employee) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="border rounded px-2 py-1">
                                                <option value="active" @selected($employee->status === 'active')>Active</option>
                                                <option value="suspended" @selected($employee->status === 'suspended')>Suspended</option>
                                                <option value="left_company" @selected($employee->status === 'left_company')>Left company</option>
                                            </select>
                                            <button class="px-3 py-1 bg-slate-800 text-white rounded">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $employees->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
