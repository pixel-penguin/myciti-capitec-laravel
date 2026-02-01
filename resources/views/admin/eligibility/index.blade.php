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
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Eligibility List</h3>
                    <a href="{{ route('admin.eligibility.create') }}" class="px-4 py-2 bg-slate-900 text-white rounded">Add / Import</a>
                </div>
            </div>

            <div class="bg-white shadow sm:rounded-lg p-6">
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
                                        <a href="{{ route('admin.eligibility.edit', $employee) }}" class="px-3 py-1 bg-slate-800 text-white rounded">Edit</a>
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
