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
            $raw = \Ikromjon\NativePHP\SocialAuth\SocialAuth::$lastRawResponse;
            $this->log('RAW bridge response', $raw ? json_decode($raw, true) : 'NULL');
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
    public function onAppleSignIn($data = [])
    {
        $this->log('EVENT: AppleSignInCompleted', $data);
        $this->loading = false;
    }

    #[OnNative(GoogleSignInCompleted::class)]
    public function onGoogleSignIn($data = [])
    {
        $this->log('EVENT: GoogleSignInCompleted', $data);
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

            $this->log('Saved identity from event, redirecting');
            return $this->redirect('/identities');
        }
    }

    #[OnNative(SignInFailed::class)]
    public function onSignInFailed($data = [])
    {
        $this->log('EVENT: SignInFailed', $data);
        $this->loading = false;
        $errorCode = $data['errorCode'] ?? '';
        if ($errorCode !== 'CANCELED') {
            $this->error = $data['error'] ?? 'Sign-in failed. Please try again.';
        }
    }

    public function render()
    {
        return view('livewire.welcome-screen');
    }
}
