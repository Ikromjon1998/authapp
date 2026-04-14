<?php

namespace App\Livewire;

use Ikromjon\NativePHP\SocialAuth\Facades\SocialAuth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class IdentityList extends Component
{
    public array $identities = [];
    public array $credentialStates = [];

    public function mount()
    {
        $this->identities = session('identities', []);
        if (empty($this->identities)) {
            return $this->redirect('/');
        }
        $this->checkCredentialStates();
    }

    public function checkCredentialStates()
    {
        $this->credentialStates = [];
        foreach ($this->identities as $index => $identity) {
            if (($identity['provider'] ?? '') === 'apple' && ! empty($identity['userId'])) {
                $this->credentialStates[$index] = SocialAuth::checkAppleCredentialState($identity['userId']);
            }
        }
    }

    public function removeIdentity(int $index)
    {
        $identity = $this->identities[$index] ?? null;
        if (! $identity) {
            return;
        }

        if (($identity['provider'] ?? '') === 'google') {
            SocialAuth::signOut();
        }

        unset($this->identities[$index]);
        $this->identities = array_values($this->identities);
        session(['identities' => $this->identities]);

        if (empty($this->identities)) {
            return $this->redirect('/');
        }

        $this->checkCredentialStates();
    }

    public function render()
    {
        return view('livewire.identity-list');
    }
}
