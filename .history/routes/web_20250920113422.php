<?php

use App\Livewire\Actions\Logout;
use App\Livewire\Auth\Login;
use App\Livewire\Home\Index;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;



// Login (guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// Logout (auth)
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->middleware('auth')->name('logout');

// Dashboard (auth)
Route::middleware('auth')->group(function () {
    Route::get('/', Index::class)->name('home');
    Route::get('/dashboard', Index::class)->name('dashboard');
});

// Redireciona '/' para dashboard
Route::redirect('/', '/dashboard');

// Fallback: se alguém tentar acessar qualquer rota não definida
Route::fallback(function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
});
