<div class="flex-1 flex flex-col">
    {{-- Header --}}
    <div class="bg-white border-b px-4 py-3 flex items-center justify-between">
        <a href="/identities" class="text-indigo-600 font-medium flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back
        </a>
        <h1 class="font-semibold">Identity Detail</h1>
        <button wire:click="toggleDisconnectConfirm" class="text-red-600 font-medium text-sm">Disconnect</button>
    </div>

    @if($identity)
        <div class="flex-1 overflow-y-auto">
            {{-- Profile Card --}}
            <div class="bg-white pt-8 pb-6 px-4 text-center border-b">
                @if(($identity['provider'] ?? '') === 'google' && !empty($identity['photoUrl']))
                    <img src="{{ $identity['photoUrl'] }}" class="w-24 h-24 rounded-full object-cover mx-auto mb-4" />
                @else
                    <div class="w-24 h-24 rounded-full {{ ($identity['provider'] ?? '') === 'apple' ? 'bg-black' : 'bg-blue-500' }} flex items-center justify-center text-white font-bold text-3xl mx-auto mb-4">
                        {{ strtoupper(substr($identity['givenName'] ?? $identity['displayName'] ?? $identity['email'] ?? '?', 0, 1)) }}
                    </div>
                @endif
                <h2 class="text-2xl font-bold">
                    {{ $identity['displayName'] ?? ($identity['givenName'] ?? '') . ' ' . ($identity['familyName'] ?? '') }}
                </h2>
                <p class="text-gray-500 mt-1">{{ $identity['email'] ?? 'No email available' }}</p>
                <span class="inline-block mt-2 text-xs px-3 py-1 rounded-full {{ ($identity['provider'] ?? '') === 'apple' ? 'bg-black text-white' : 'bg-blue-100 text-blue-700' }}">
                    {{ ucfirst($identity['provider'] ?? 'unknown') }}
                </span>
            </div>

            {{-- Apple First Sign-In Warning --}}
            @if(($identity['provider'] ?? '') === 'apple' && empty($identity['email']) && empty($identity['givenName']))
                <div class="mx-4 mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <p class="text-amber-700 text-sm">Apple only shares your name and email on first sign-in. This data was not available for this session.</p>
                </div>
            @endif

            {{-- Profile Section --}}
            <div class="bg-white mt-3 border-y">
                <div class="px-4 py-2 bg-gray-50 border-b border-gray-100">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Profile</span>
                </div>
                @foreach([
                    'Display Name' => $identity['displayName'] ?? null,
                    'First Name' => $identity['givenName'] ?? null,
                    'Last Name' => $identity['familyName'] ?? null,
                    'Email' => $identity['email'] ?? null,
                    'Photo URL' => $identity['photoUrl'] ?? null,
                ] as $label => $value)
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                        <span class="text-sm text-gray-500">{{ $label }}</span>
                        <span class="text-sm font-medium truncate ml-4 max-w-[200px] {{ $value ? '' : 'text-gray-300' }}">
                            {{ $value ?? 'Not provided' }}
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- Provider Details --}}
            <div class="bg-white mt-3 border-y">
                <div class="px-4 py-2 bg-gray-50 border-b border-gray-100">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Provider Details</span>
                </div>
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <span class="text-sm text-gray-500">Provider</span>
                    <span class="text-sm font-medium">{{ ucfirst($identity['provider'] ?? 'unknown') }}</span>
                </div>
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <span class="text-sm text-gray-500">User ID</span>
                    <span class="text-xs font-mono text-gray-600 truncate ml-4 max-w-[200px]">{{ $identity['userId'] ?? 'N/A' }}</span>
                </div>
                @if(!empty($identity['realUserStatus']))
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                        <span class="text-sm text-gray-500">Real User Status</span>
                        <span class="text-sm font-medium {{ ($identity['realUserStatus'] ?? '') === 'likelyReal' ? 'text-green-600' : 'text-gray-600' }}">
                            {{ $identity['realUserStatus'] }}
                        </span>
                    </div>
                @endif
                @if(!empty($identity['_signedInAt']))
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                        <span class="text-sm text-gray-500">Signed In At</span>
                        <span class="text-sm font-medium">{{ \Carbon\Carbon::parse($identity['_signedInAt'])->format('M j, Y g:i A') }}</span>
                    </div>
                @endif
            </div>

            {{-- Apple Credential State --}}
            @if(($identity['provider'] ?? '') === 'apple')
                <div class="bg-white mt-3 border-y">
                    <div class="px-4 py-2 bg-gray-50 border-b border-gray-100">
                        <span class="text-xs font-semibold text-gray-500 uppercase">Credential State</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3">
                        <div>
                            <span class="text-sm text-gray-500">Current State</span>
                            <span class="ml-2 text-xs px-2 py-0.5 rounded-full
                                {{ $credentialState === 'authorized' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $credentialState === 'revoked' ? 'bg-red-100 text-red-700' : '' }}
                                {{ $credentialState === 'not_found' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ !in_array($credentialState, ['authorized', 'revoked', 'not_found']) ? 'bg-gray-100 text-gray-500' : '' }}
                            ">
                                {{ str_replace('_', ' ', $credentialState ?: 'checking...') }}
                            </span>
                        </div>
                        <button wire:click="refreshCredentialState" class="text-indigo-600 text-sm font-medium">
                            Refresh
                        </button>
                    </div>
                </div>
            @endif

            {{-- Security / Tokens --}}
            <div class="bg-white mt-3 border-y">
                <div class="px-4 py-2 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Security Tokens</span>
                    <button wire:click="toggleTokens" class="text-indigo-600 text-xs font-medium">
                        {{ $showTokens ? 'Hide' : 'Show' }}
                    </button>
                </div>
                @foreach([
                    'Identity Token' => $identity['identityToken'] ?? null,
                    'Authorization Code' => $identity['authorizationCode'] ?? null,
                    'Access Token' => $identity['accessToken'] ?? null,
                    'Nonce (raw)' => $identity['_nonce'] ?? null,
                    'State' => $identity['_state'] ?? ($identity['state'] ?? null),
                ] as $label => $value)
                    @if($value)
                        <div class="px-4 py-3 border-b border-gray-100">
                            <span class="text-xs text-gray-400">{{ $label }}</span>
                            @if($showTokens)
                                <p class="text-xs font-mono text-gray-600 mt-1 break-all">{{ $value }}</p>
                            @else
                                <p class="text-xs font-mono text-gray-300 mt-1">{{ str_repeat('*', min(40, strlen($value))) }}</p>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="h-8"></div>
        </div>
    @else
        <div class="flex-1 flex items-center justify-center">
            <p class="text-gray-400">Identity not found</p>
        </div>
    @endif

    {{-- Disconnect Confirmation --}}
    @if($confirmDisconnect)
        <div class="fixed inset-0 bg-black/50 flex items-end justify-center z-50">
            <div class="bg-white w-full max-w-md rounded-t-2xl p-6 safe-bottom">
                <h3 class="text-lg font-bold mb-2">Disconnect Identity?</h3>
                @if(($identity['provider'] ?? '') === 'apple')
                    <p class="text-gray-500 text-sm mb-2">This will remove the Apple identity from your wallet.</p>
                    <p class="text-amber-600 text-xs mb-6">Note: Apple Sign-In has no sign-out API. To fully revoke access, go to Settings > Apple ID > Sign-In & Security > Sign in with Apple.</p>
                @else
                    <p class="text-gray-500 text-sm mb-6">This will sign you out of Google and remove this identity from your wallet.</p>
                @endif
                <button wire:click="disconnect" class="w-full bg-red-600 text-white rounded-xl py-3 font-semibold mb-3">
                    Disconnect
                </button>
                <button wire:click="toggleDisconnectConfirm" class="w-full bg-gray-100 text-gray-700 rounded-xl py-3 font-semibold">
                    Cancel
                </button>
            </div>
        </div>
    @endif
</div>
