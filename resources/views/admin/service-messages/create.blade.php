<x-admin-layout>
    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Create New Notice</h2>

                <form method="POST" action="{{ route('admin.service-messages.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notice Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" placeholder="Enter notice title" class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-[#0077C8] focus:border-[#0077C8]" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-red-500">*</span> <span class="text-gray-400 font-normal">(Max 500 characters)</span></label>
                        <textarea name="body" rows="4" maxlength="500" placeholder="Enter notice message" class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-[#0077C8] focus:border-[#0077C8]" id="bodyField">{{ old('body') }}</textarea>
                        <p class="text-xs text-gray-400 mt-1"><span id="charCount">0</span>/500 characters</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image (Optional)</label>
                        <input type="file" name="image" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[#0077C8] file:text-white hover:file:bg-[#005A9E] file:cursor-pointer">
                        <p class="text-xs text-gray-400 mt-1">Recommended size: 800x400px. Max 5MB.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                        <select name="level" class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-[#0077C8] focus:border-[#0077C8]">
                            <option value="info" @selected(old('level', 'info') === 'info')>Info</option>
                            <option value="warning" @selected(old('level') === 'warning')>Warning</option>
                            <option value="alert" @selected(old('level') === 'alert')>Alert</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-[#0077C8] focus:border-[#0077C8]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-[#0077C8] focus:border-[#0077C8]">
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                        Active
                    </label>

                    @if ($errors->any())
                        <div class="rounded-lg bg-rose-50 p-3 text-rose-700 text-sm">
                            <ul class="list-disc ml-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="flex items-center gap-3">
                        <button class="px-5 py-2 bg-[#0077C8] hover:bg-[#005A9E] text-white text-sm font-medium rounded-lg transition-colors">Create Notice</button>
                        <a href="{{ route('admin.service-messages.index') }}" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const bodyField = document.getElementById('bodyField');
        const charCount = document.getElementById('charCount');
        bodyField.addEventListener('input', () => { charCount.textContent = bodyField.value.length; });
        charCount.textContent = bodyField.value.length;
    </script>
</x-admin-layout>
