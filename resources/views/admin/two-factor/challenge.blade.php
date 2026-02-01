<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Two-Factor Verification
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
                @if (session('status'))
                    <div class="rounded bg-green-50 p-3 text-green-800">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.two-factor.send') }}">
                    @csrf
                    <button class="px-4 py-2 bg-slate-900 text-white rounded w-full">Send OTP to Email</button>
                </form>

                <form method="POST" action="{{ route('admin.two-factor.verify') }}" class="space-y-2">
                    @csrf
                    <label class="block text-sm text-slate-600">Enter OTP</label>
                    <input type="text" name="code" class="border rounded px-3 py-2 w-full" required>
                    @error('code')
                        <div class="text-sm text-rose-600">{{ $message }}</div>
                    @enderror
                    <button class="px-4 py-2 bg-emerald-600 text-white rounded w-full">Verify</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
