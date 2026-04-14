<?php

namespace App\Livewire;

use Ikromjon\NativePHP\SocialAuth\Facades\SocialAuth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class IdentityDetail extends Component
{
    public int $index;
    public ?array $identity = null;
    public string $credentialState = '';
    public bool $showTokens = false;
    public bool $confirmDisconnect = false;

    public function mount(int $index)
    {
        $this->index = $index;
        $identities = session('identities', []);
        $this->identity = $identities[$index] ?? null;

        if (! $this->identity) {
            return $this->redirect('/identities');
        }

        if (($this->identity['provider'] ?? '') === 'apple' && ! empty($this->identity['userId'])) {
            $this->credentialState = SocialAuth::checkAppleCredentialState($this->identity['userId']);
        }
    }

    public function refreshCredentialState()
    {
        if (($this->identity['provider'] ?? '') === 'apple' && ! empty($this->identity['userId'])) {
            $this->credentialState = SocialAuth::checkAppleCredentialState($this->identity['userId']);
        }
    }

    public function toggleTokens()
    {
        $this->showTokens = ! $this->showTokens;
    }

    public function toggleDisconnectConfirm()
    {
        $this->confirmDisconnect = ! $this->confirmDisconnect;
    }

    public function disconnect()
    {
        if (($this->identity['provider'] ?? '') === 'google') {
            SocialAuth::signOut();
        }

        $identities = session('identities', []);
        unset($identities[$this->index]);
        session(['identities' => array_values($identities)]);

        return $this->redirect('/identities');
    }

    public function render()
    {
        return view('livewire.identity-detail');
    }
}
