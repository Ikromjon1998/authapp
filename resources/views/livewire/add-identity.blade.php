<div class="flex-1 flex flex-col">
    {{-- Header --}}
    <div class="bg-white border-b px-4 py-3 flex items-center justify-between">
        <a href="/identities" class="text-indigo-600 font-medium flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back
        </a>
        <h1 class="font-semibold">Add Identity</h1>
        <div class="w-12"></div>
    </div>

    <div class="flex-1 flex flex-col items-center justify-center p-8 text-center">
        <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
        </div>

        <h2 class="text-xl font-bold mb-2">Connect Another Account</h2>
        <p class="text-gray-500 mb-8 text-sm">Add another social identity to your wallet.</p>

        @if($error)
            <div class="w-full bg-red-50 border border-red-200 rounded-lg p-3 mb-6 text-left">
                <p class="text-red-700 text-sm">{{ $error }}</p>
            </div>
        @endif

        {{-- Already Connected Indicators --}}
        @if(!empty($connectedProviders))
            <div class="w-full mb-4">
                <p class="text-xs text-gray-400 mb-2">Already connected:</p>
                <div class="flex justify-center gap-2">
                    @foreach($connectedProviders as $provider)
                        <span class="text-xs px-3 py-1 rounded-full {{ $provider === 'apple' ? 'bg-black text-white' : 'bg-blue-100 text-blue-700' }}">
                            {{ ucfirst($provider) }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Apple Sign-In Button --}}
        <button
            wire:click="signInWithApple"
            wire:loading.attr="disabled"
            class="w-full flex items-center justify-center gap-3 bg-black text-white rounded-xl py-3.5 font-semibold text-base mb-3 active:bg-gray-800 disabled:opacity-50"
        >
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.05 20.28c-.98.95-2.05.88-3.08.4-1.09-.5-2.08-.48-3.24 0-1.44.62-2.2.44-3.06-.4C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
            </svg>
            Sign in with Apple
        </button>

        {{-- Google Sign-In Button --}}
        <button
            wire:click="signInWithGoogle"
            wire:loading.attr="disabled"
            class="w-full flex items-center justify-center gap-3 bg-white text-gray-700 border border-gray-300 rounded-xl py-3.5 font-semibold text-base active:bg-gray-50 disabled:opacity-50"
        >
            <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Sign in with Google
        </button>

        <p class="text-xs text-gray-400 mt-6">Apple Sign-In is available on iOS only</p>
    </div>
</div>
