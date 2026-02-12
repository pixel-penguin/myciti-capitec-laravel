<x-admin-layout>
    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Edit Notice</h2>

                <form method="POST" action="{{ route('admin.service-messages.update', $message) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notice Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $message->title) }}" placeholder="Enter notice title" class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-[#0077C8] focus:border-[#0077C8]" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-red-500">*</span> <span class="text-gray-400 font-normal">(Max 500 characters)</span></label>
                        <textarea name="body" rows="4" maxlength="500" placeholder="Enter notice message" class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-[#0077C8] focus:border-[#0077C8]" id="bodyField">{{ old('body', $message->body) }}</textarea>
                        <p class="text-xs text-gray-400 mt-1"><span id="charCount">0</span>/500 characters</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image (Optional)</label>
                        <input type="hidden" name="image_key" id="imageKeyInput" value="">
                        <input type="hidden" name="remove_image" id="removeImageInput" value="0">

                        {{-- Existing image preview --}}
                        <div id="existingImageContainer" class="{{ $message->image_path ? '' : 'hidden' }} mb-3">
                            <div class="flex items-start gap-4">
                                <img src="{{ \App\Http\Controllers\Admin\ServiceMessageController::imageUrl($message->image_path) }}" alt="Notice image" class="w-40 h-24 object-cover rounded-lg border border-gray-200">
                                <button type="button" id="removeExistingBtn" class="text-sm text-red-600 hover:text-red-800 mt-2">Remove image</button>
                            </div>
                        </div>

                        {{-- Upload area --}}
                        <div id="uploadArea" class="{{ $message->image_path ? 'hidden' : '' }} relative border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-[#0077C8] transition-colors">
                            <input type="file" id="imageFileInput" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div id="uploadPlaceholder">
                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <p class="mt-1 text-sm text-gray-500">Click or drag an image here</p>
                            </div>
                            <div id="uploadProgress" class="hidden">
                                <div class="flex items-center justify-center gap-2">
                                    <svg class="animate-spin h-5 w-5 text-[#0077C8]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <span class="text-sm text-gray-600">Uploading...</span>
                                </div>
                            </div>
                        </div>

                        {{-- New image preview (after upload) --}}
                        <div id="newImagePreviewContainer" class="hidden mt-3">
                            <div class="flex items-start gap-4">
                                <img id="newImagePreview" src="" alt="Preview" class="w-40 h-24 object-cover rounded-lg border border-gray-200">
                                <button type="button" id="removeNewImageBtn" class="text-sm text-red-600 hover:text-red-800 mt-2">Remove</button>
                            </div>
                        </div>

                        <div id="uploadError" class="hidden mt-2 text-sm text-red-600"></div>
                        <p class="text-xs text-gray-400 mt-1">Recommended size: 800x400px. Images are automatically resized. Upload a new image to replace the current one.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                        <select name="level" class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-[#0077C8] focus:border-[#0077C8]">
                            <option value="info" @selected(old('level', $message->level) === 'info')>Info</option>
                            <option value="warning" @selected(old('level', $message->level) === 'warning')>Warning</option>
                            <option value="alert" @selected(old('level', $message->level) === 'alert')>Alert</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $message->starts_at?->format('Y-m-d\TH:i')) }}" class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-[#0077C8] focus:border-[#0077C8]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $message->ends_at?->format('Y-m-d\TH:i')) }}" class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-[#0077C8] focus:border-[#0077C8]">
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $message->is_active))>
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
                        <button class="px-5 py-2 bg-[#0077C8] hover:bg-[#005A9E] text-white text-sm font-medium rounded-lg transition-colors">Save Changes</button>
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

        (function() {
            const fileInput = document.getElementById('imageFileInput');
            const imageKeyInput = document.getElementById('imageKeyInput');
            const removeImageInput = document.getElementById('removeImageInput');
            const uploadArea = document.getElementById('uploadArea');
            const placeholder = document.getElementById('uploadPlaceholder');
            const progress = document.getElementById('uploadProgress');
            const existingContainer = document.getElementById('existingImageContainer');
            const newPreviewContainer = document.getElementById('newImagePreviewContainer');
            const newPreviewImg = document.getElementById('newImagePreview');
            const removeExistingBtn = document.getElementById('removeExistingBtn');
            const removeNewBtn = document.getElementById('removeNewImageBtn');
            const errorEl = document.getElementById('uploadError');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const hasExistingImage = {{ $message->image_path ? 'true' : 'false' }};

            function showError(msg) {
                errorEl.textContent = msg;
                errorEl.classList.remove('hidden');
                setTimeout(() => errorEl.classList.add('hidden'), 5000);
            }

            // Remove existing image
            removeExistingBtn.addEventListener('click', function() {
                existingContainer.classList.add('hidden');
                removeImageInput.value = '1';
                uploadArea.classList.remove('hidden');
            });

            // Remove newly uploaded image
            removeNewBtn.addEventListener('click', function() {
                imageKeyInput.value = '';
                fileInput.value = '';
                newPreviewContainer.classList.add('hidden');
                // If there was an existing image, restore it; otherwise show upload area
                if (hasExistingImage && removeImageInput.value === '0') {
                    existingContainer.classList.remove('hidden');
                } else {
                    uploadArea.classList.remove('hidden');
                }
                placeholder.classList.remove('hidden');
                progress.classList.add('hidden');
            });

            function resizeImage(file, maxW, maxH) {
                return new Promise((resolve) => {
                    const img = new Image();
                    img.onload = () => {
                        let { width, height } = img;
                        if (width > maxW || height > maxH) {
                            const ratio = Math.min(maxW / width, maxH / height);
                            width = Math.round(width * ratio);
                            height = Math.round(height * ratio);
                        }
                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        canvas.getContext('2d').drawImage(img, 0, 0, width, height);
                        const type = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
                        canvas.toBlob((blob) => resolve(blob), type, 0.9);
                    };
                    img.src = URL.createObjectURL(file);
                });
            }

            fileInput.addEventListener('change', async function() {
                const file = this.files[0];
                if (!file) return;

                if (!file.type.startsWith('image/')) {
                    showError('Please select an image file.');
                    return;
                }

                errorEl.classList.add('hidden');
                placeholder.classList.add('hidden');
                progress.classList.remove('hidden');

                try {
                    const resized = await resizeImage(file, 1920, 1920);
                    const contentType = resized.type;

                    // 1. Get presigned URL
                    const res = await fetch('{{ route("admin.service-messages.presigned-upload") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            content_type: contentType,
                            filename: file.name,
                        }),
                    });

                    if (!res.ok) {
                        const err = await res.json();
                        throw new Error(err.message || 'Failed to get upload URL.');
                    }

                    const { url, key } = await res.json();

                    // 2. Upload resized image directly to S3
                    const putRes = await fetch(url, {
                        method: 'PUT',
                        headers: { 'Content-Type': contentType },
                        body: resized,
                    });

                    if (!putRes.ok) {
                        throw new Error('Upload to storage failed.');
                    }

                    // 3. Show preview and set key
                    imageKeyInput.value = key;
                    removeImageInput.value = '0';
                    newPreviewImg.src = URL.createObjectURL(resized);
                    newPreviewContainer.classList.remove('hidden');
                    existingContainer.classList.add('hidden');
                    uploadArea.classList.add('hidden');
                } catch (err) {
                    showError(err.message || 'Upload failed. Please try again.');
                    placeholder.classList.remove('hidden');
                    progress.classList.add('hidden');
                }
            });
        })();
    </script>
</x-admin-layout>
