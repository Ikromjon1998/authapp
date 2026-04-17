<?php

namespace App\Livewire;

use Ikromjon\NativePHP\SocialAuth\Events\AppleSignInCompleted;
use Ikromjon\NativePHP\SocialAuth\Events\GoogleSignInCompleted;
use Ikromjon\NativePHP\SocialAuth\Events\SignInFailed;
use Ikromjon\NativePHP\SocialAuth\Facades\SocialAuth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Native\Mobile\Attributes\OnNative;

#[Layout('components.layouts.app')]
class AddIdentity extends Component
{
    public ?string $error = null;
    public bool $loading = false;
    public array $connectedProviders = [];

    public function mount()
    {
        $identities = session('identities', []);
        $this->connectedProviders = array_unique(array_column($identities, 'provider'));
    }

    public function signInWithApple()
    {
        $this->error = null;
        $this->loading = true;

        $rawNonce = bin2hex(random_bytes(16));
        $state = bin2hex(random_bytes(16));
        session(['auth_nonce' => $rawNonce, 'auth_state' => $state]);

        $result = SocialAuth::appleSignIn(
            scopes: ['email', 'fullName'],
            nonce: hash('sha256', $rawNonce),
            state: $state,
        );

        if ($result) {
            $identity = $result->toArray();
            $identity['_nonce'] = $rawNonce;
            $identity['_state'] = $state;
            $identity['_signedInAt'] = now()->toIso8601String();

            $identities = session('identities', []);
            $identities[] = $identity;
            session(['identities' => $identities]);

            return $this->redirect('/identities');
        }

        $this->loading = false;
    }

    public function signInWithGoogle()
    {
        $this->error = null;
        $this->loading = true;

        $nonce = bin2hex(random_bytes(16));
        session(['auth_nonce' => $nonce]);

        $result = SocialAuth::googleSignIn(nonce: $nonce);

        if ($result) {
            $identity = $result->toArray();
            $identity['_nonce'] = $nonce;
            $identity['_signedInAt'] = now()->toIso8601String();

            $identities = session('identities', []);
            $identities[] = $identity;
            session(['identities' => $identities]);

            return $this->redirect('/identities');
        }

        $this->loading = false;
    }

    #[OnNative(AppleSignInCompleted::class)]
    public function onAppleSignIn(
        string $userId = '',
        ?string $identityToken = null,
        ?string $authorizationCode = null,
        ?string $email = null,
        ?string $givenName = null,
        ?string $familyName = null,
    ) {
        $this->loading = false;

        if (! empty($userId)) {
            $identity = [
                'provider' => 'apple',
                'userId' => $userId,
                'identityToken' => $identityToken ?? '',
                'authorizationCode' => $authorizationCode ?? '',
                'email' => $email ?? '',
                'givenName' => $givenName ?? '',
                'familyName' => $familyName ?? '',
                'displayName' => trim(($givenName ?? '') . ' ' . ($familyName ?? '')),
                '_nonce' => session('auth_nonce', ''),
                '_signedInAt' => now()->toIso8601String(),
            ];

            $identities = session('identities', []);
            $identities[] = $identity;
            session(['identities' => $identities]);

            return $this->redirect('/identities');
        }
    }

    #[OnNative(GoogleSignInCompleted::class)]
    public function onGoogleSignIn(
        string $userId = '',
        ?string $identityToken = null,
        ?string $email = null,
        ?string $displayName = null,
        ?string $givenName = null,
        ?string $familyName = null,
        ?string $photoUrl = null,
    ) {
        $this->loading = false;

        if (! empty($userId)) {
            $identity = [
                'provider' => 'google',
                'userId' => $userId,
                'identityToken' => $identityToken ?? '',
                'email' => $email ?? '',
                'displayName' => $displayName ?? '',
                'givenName' => $givenName ?? '',
                'familyName' => $familyName ?? '',
                'photoUrl' => $photoUrl ?? '',
                '_nonce' => session('auth_nonce', ''),
                '_signedInAt' => now()->toIso8601String(),
            ];

            $identities = session('identities', []);
            $identities[] = $identity;
            session(['identities' => $identities]);

            return $this->redirect('/identities');
        }
    }

    #[OnNative(SignInFailed::class)]
    public function onSignInFailed(
        string $provider = '',
        string $error = '',
        ?string $errorCode = null,
    ) {
        $this->loading = false;
        if ($errorCode !== 'CANCELED') {
            $this->error = ! empty($error) ? $error : 'Sign-in failed. Please try again.';
        }
    }

    public function render()
    {
        return view('livewire.add-identity');
    }
}
