<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class Login extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string|min:3')]
    public string $password = '';

    public bool $remember = false;

    public function mount(): void
    {
        // Se já estiver logado, manda para o dashboard
        if (Auth::check()) {
            redirect()->intended(route('dashboard'));
        }
    }

    public function login(): void
    {
        $this->validate();

        // throttle simples (5 tentativas / 60s)
        if ($this->tooManyAttempts()) {
            $this->addError('email', 'Muitas tentativas. Tente novamente em alguns segundos.');
            return;
        }

        if (Auth::attempt([
            'email' => $this->email,
            'password' => $this->password,
        ], $this->remember)) {
            RateLimiter::clear($this->throttleKey());
            session()->regenerate();
            redirect()->intended(route('dashboard'));
            return;
        }

        // falha → registra tentativa
        RateLimiter::hit($this->throttleKey(), 60);
        $this->addError('email', 'Credenciais inválidas.');
    }

    private function tooManyAttempts(): bool
    {
        return RateLimiter::tooManyAttempts($this->throttleKey(), 5);
    }

    private function throttleKey(): string
    {
        return Str::lower($this->email).'|'.request()->ip();
    }

    
    public function render()
    {
        return view('livewire.auth.login');
    }
}
