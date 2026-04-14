<div class="flex-1 flex flex-col">
    {{-- Header --}}
    <div class="bg-white border-b px-4 pt-2 pb-3">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">My Identities</h1>
            <a href="/identities/add" class="w-9 h-9 bg-indigo-600 text-white rounded-full flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </a>
        </div>
        <p class="text-sm text-gray-400 mt-1">{{ count($identities) }} connected {{ count($identities) === 1 ? 'identity' : 'identities' }}</p>
    </div>

    {{-- Identity Cards --}}
    <div class="flex-1 overflow-y-auto p-4 space-y-3">
        @foreach($identities as $index => $identity)
            <a href="/identities/{{ $index }}" class="block bg-white rounded-xl border border-gray-200 p-4 active:bg-gray-50">
                <div class="flex items-center gap-3">
                    {{-- Provider Icon + Avatar --}}
                    @if(($identity['provider'] ?? '') === 'google' && !empty($identity['photoUrl']))
                        <img src="{{ $identity['photoUrl'] }}" class="w-12 h-12 rounded-full object-cover" />
                    @else
                        <div class="w-12 h-12 rounded-full {{ ($identity['provider'] ?? '') === 'apple' ? 'bg-black' : 'bg-blue-500' }} flex items-center justify-center text-white font-bold text-lg">
                            {{ strtoupper(substr($identity['givenName'] ?? $identity['displayName'] ?? $identity['email'] ?? '?', 0, 1)) }}
                        </div>
                    @endif

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold truncate">
                                {{ $identity['displayName'] ?? $identity['givenName'] ?? 'Unknown' }}
                                @if(!empty($identity['familyName']) && empty($identity['displayName']))
                                    {{ $identity['familyName'] }}
                                @endif
                            </p>
                            {{-- Provider Badge --}}
                            <span class="flex-shrink-0 text-xs px-2 py-0.5 rounded-full {{ ($identity['provider'] ?? '') === 'apple' ? 'bg-black text-white' : 'bg-blue-100 text-blue-700' }}">
                                {{ ucfirst($identity['provider'] ?? 'unknown') }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 truncate">{{ $identity['email'] ?? 'No email available' }}</p>

                        {{-- Credential State Badge (Apple only) --}}
                        @if(($identity['provider'] ?? '') === 'apple' && isset($credentialStates[$index]))
                            @php $state = $credentialStates[$index]; @endphp
                            <div class="mt-1">
                                <span class="text-xs px-2 py-0.5 rounded-full
                                    {{ $state === 'authorized' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $state === 'revoked' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $state === 'not_found' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ !in_array($state, ['authorized', 'revoked', 'not_found']) ? 'bg-gray-100 text-gray-500' : '' }}
                                ">
                                    Credential: {{ str_replace('_', ' ', $state) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        @endforeach
    </div>
</div>
