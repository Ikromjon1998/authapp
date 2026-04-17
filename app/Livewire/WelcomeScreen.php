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
class WelcomeScreen extends Component
{
    public ?string $error = null;
    public bool $loading = false;
    public array $logs = [];
    public bool $showLogs = false;

    private function log(string $message, mixed $data = null): void
    {
        $entry = '[' . now()->format('H:i:s.v') . '] ' . $message;
        if ($data !== null) {
            $entry .= "\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
        $this->logs[] = $entry;
    }

    public function mount()
    {
        $this->log('mount()');
        $identities = session('identities', []);
        if (! empty($identities)) {
            $this->log('Redirecting to /identities (have ' . count($identities) . ' identities)');
            return $this->redirect('/identities');
        }
        $this->log('No identities in session, showing welcome');
    }

    public function toggleLogs()
    {
        $this->showLogs = ! $this->showLogs;
    }

    public function clearLogs()
    {
        $this->logs = [];
    }

    public function signInWithApple()
    {
        $this->error = null;
        $this->loading = true;
        $this->log('signInWithApple() called');

        $rawNonce = bin2hex(random_bytes(16));
        $state = bin2hex(random_bytes(16));
        session(['auth_nonce' => $rawNonce, 'auth_state' => $state]);
        $this->log('Generated nonce & state', ['nonce' => $rawNonce, 'state' => $state]);

        try {
            $result = SocialAuth::appleSignIn(
                scopes: ['email', 'fullName'],
                nonce: hash('sha256', $rawNonce),
                state: $state,
            );
            $this->log('appleSignIn() returned', $result ? $result->toArray() : null);
        } catch (\Throwable $e) {
            $this->log('appleSignIn() EXCEPTION: ' . $e->getMessage());
            $this->error = $e->getMessage();
            $this->loading = false;
            return;
        }

        if ($result) {
            $identity = $result->toArray();
            $identity['_nonce'] = $rawNonce;
            $identity['_state'] = $state;
            $identity['_signedInAt'] = now()->toIso8601String();

            $identities = session('identities', []);
            $identities[] = $identity;
            session(['identities' => $identities]);

            $this->log('Success! Redirecting to /identities');
            return $this->redirect('/identities');
        }

        $this->log('appleSignIn() returned null (no result)');
        $this->loading = false;
    }

    public function signInWithGoogle()
    {
        $this->error = null;
        $this->loading = true;
        $this->log('signInWithGoogle() called');

        $nonce = bin2hex(random_bytes(16));
        session(['auth_nonce' => $nonce]);
        $this->log('Generated nonce', ['nonce' => $nonce]);

        try {
            $result = SocialAuth::googleSignIn(nonce: $nonce);
            $this->log('googleSignIn() returned', $result ? $result->toArray() : null);
        } catch (\Throwable $e) {
            $this->log('googleSignIn() EXCEPTION: ' . $e->getMessage());
            $this->error = $e->getMessage();
            $this->loading = false;
            return;
        }

        if ($result) {
            $identity = $result->toArray();
            $identity['_nonce'] = $nonce;
            $identity['_signedInAt'] = now()->toIso8601String();

            $identities = session('identities', []);
            $identities[] = $identity;
            session(['identities' => $identities]);

            $this->log('Success! Redirecting to /identities');
            return $this->redirect('/identities');
        }

        $this->log('googleSignIn() returned null (no result)');
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
        $this->log('EVENT: AppleSignInCompleted', compact('userId', 'identityToken', 'authorizationCode', 'email', 'givenName', 'familyName'));
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

            $this->log('Saved identity from event, redirecting');
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
        $this->log('EVENT: GoogleSignInCompleted', compact('userId', 'identityToken', 'email', 'displayName', 'givenName', 'familyName', 'photoUrl'));
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

            $this->log('Saved identity from event, redirecting');
            return $this->redirect('/identities');
        }
    }

    #[OnNative(SignInFailed::class)]
    public function onSignInFailed(
        string $provider = '',
        string $error = '',
        ?string $errorCode = null,
    ) {
        $this->log('EVENT: SignInFailed', compact('provider', 'error', 'errorCode'));
        $this->loading = false;
        if ($errorCode !== 'CANCELED') {
            $this->error = ! empty($error) ? $error : 'Sign-in failed. Please try again.';
        }
    }

    public function render()
    {
        return view('livewire.welcome-screen');
    }
}
