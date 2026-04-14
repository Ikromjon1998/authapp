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
    public function onAppleSignIn($data = [])
    {
        $this->loading = false;
    }

    #[OnNative(GoogleSignInCompleted::class)]
    public function onGoogleSignIn($data = [])
    {
        $this->loading = false;

        if (! empty($data)) {
            $identity = [
                'provider' => 'google',
                'userId' => $data['userId'] ?? '',
                'identityToken' => $data['identityToken'] ?? '',
                'email' => $data['email'] ?? '',
                'displayName' => $data['displayName'] ?? '',
                'givenName' => $data['givenName'] ?? '',
                'familyName' => $data['familyName'] ?? '',
                'photoUrl' => $data['photoUrl'] ?? '',
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
    public function onSignInFailed($data = [])
    {
        $this->loading = false;
        $errorCode = $data['errorCode'] ?? '';
        if ($errorCode !== 'CANCELED') {
            $this->error = $data['error'] ?? 'Sign-in failed. Please try again.';
        }
    }

    public function render()
    {
        return view('livewire.add-identity');
    }
}
