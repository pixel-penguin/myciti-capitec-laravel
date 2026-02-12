<x-guest-layout>
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            {{-- Logo & Header --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-[#0077C8] rounded-2xl mb-4">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-gray-900">Two-Factor Verification</h1>
                <p class="text-sm text-gray-500 mt-1">Enter the 6-digit code sent to your email</p>
            </div>

            @if (session('status'))
                <div class="rounded-lg bg-green-50 border border-green-200 p-3 text-green-800 text-sm mb-4">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.two-factor.verify') }}" id="otpForm">
                @csrf

                {{-- Hidden field to collect the full code --}}
                <input type="hidden" name="code" id="otpCode">

                {{-- 6 Individual Digit Boxes --}}
                <div class="flex justify-center gap-3 mb-6" x-data="otpInput()">
                    @for ($i = 0; $i < 6; $i++)
                        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                               class="w-12 h-14 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0077C8] focus:border-[#0077C8] transition-colors"
                               x-ref="digit{{ $i }}"
                               @input="handleInput($event, {{ $i }})"
                               @keydown.backspace="handleBackspace($event, {{ $i }})"
                               @paste="handlePaste($event)"
                               data-index="{{ $i }}">
                    @endfor
                </div>

                @error('code')
                    <div class="text-sm text-red-600 text-center mb-4">{{ $message }}</div>
                @enderror

                <button type="submit" class="w-full py-2.5 px-4 bg-[#0077C8] hover:bg-[#005A9E] text-white text-sm font-semibold rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0077C8]">
                    Verify & Sign In
                </button>
            </form>

            <div class="flex items-center justify-between mt-6">
                <form method="POST" action="{{ route('admin.two-factor.send') }}">
                    @csrf
                    <button type="submit" class="text-sm text-[#0077C8] hover:text-[#005A9E] font-medium">
                        Resend code
                    </button>
                </form>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 font-medium">
                        Back to login
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('otpInput', () => ({
                handleInput(event, index) {
                    const value = event.target.value;
                    if (!/^\d$/.test(value)) {
                        event.target.value = '';
                        return;
                    }
                    if (index < 5) {
                        this.$refs['digit' + (index + 1)].focus();
                    }
                    this.collectCode();
                },
                handleBackspace(event, index) {
                    if (event.target.value === '' && index > 0) {
                        this.$refs['digit' + (index - 1)].focus();
                    }
                },
                handlePaste(event) {
                    event.preventDefault();
                    const paste = (event.clipboardData || window.clipboardData).getData('text').trim();
                    if (!/^\d{6}$/.test(paste)) return;
                    for (let i = 0; i < 6; i++) {
                        this.$refs['digit' + i].value = paste[i];
                    }
                    this.$refs['digit5'].focus();
                    this.collectCode();
                },
                collectCode() {
                    let code = '';
                    for (let i = 0; i < 6; i++) {
                        code += this.$refs['digit' + i].value || '';
                    }
                    document.getElementById('otpCode').value = code;
                }
            }));
        });

        // Collect code before form submit
        document.getElementById('otpForm').addEventListener('submit', function() {
            let code = '';
            for (let i = 0; i < 6; i++) {
                const el = document.querySelector('[data-index="' + i + '"]');
                if (el) code += el.value || '';
            }
            document.getElementById('otpCode').value = code;
        });
    </script>
</x-guest-layout>
