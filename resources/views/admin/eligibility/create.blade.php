<x-admin-layout>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($errors->any())
                <div class="rounded bg-rose-50 p-4 text-rose-700">
                    {{ $errors->first() }}
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
                <h3 class="text-lg font-semibold mb-4">Add Employee</h3>
                <form method="POST" action="{{ route('admin.eligibility.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    @csrf
                    <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" class="border rounded px-3 py-2" required>
                    <input type="text" name="first_name" placeholder="First name" value="{{ old('first_name') }}" class="border rounded px-3 py-2">
                    <input type="text" name="last_name" placeholder="Last name" value="{{ old('last_name') }}" class="border rounded px-3 py-2">
                    <input type="text" name="phone" placeholder="Phone" value="{{ old('phone') }}" class="border rounded px-3 py-2">
                    <select name="status" class="border rounded px-3 py-2">
                        <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                        <option value="suspended" @selected(old('status') === 'suspended')>Suspended</option>
                        <option value="left_company" @selected(old('status') === 'left_company')>Left company</option>
                    </select>
                    <button class="md:col-span-5 px-4 py-2 bg-slate-900 text-white rounded">Save</button>
                </form>
            </div>

            <div>
                <a href="{{ route('admin.eligibility.index') }}" class="text-sm text-slate-600">Back to list</a>
            </div>
        </div>
    </div>
</x-admin-layout>
