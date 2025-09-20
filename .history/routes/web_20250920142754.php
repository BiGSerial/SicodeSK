<?php

use App\Livewire\Auth\Login;
use App\Livewire\Home\Index;
use App\Livewire\Tickets\CreateWizard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Regras:
| - Uma única definição para "/" que decide entre login e dashboard.
| - Rotas de guest isoladas (login com throttle).
| - Rotas autenticadas isoladas (dashboard, tickets).
| - Logout via POST seguro.
| - Fallback simples que reaproveita a decisão de "/".
*/

// Raiz: decide para onde ir, sem duplicar lógica
Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
})->name('root');

// Guest
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)
        ->middleware('throttle:login')
        ->name('login');
});

// Authenticated
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Index::class)->name('dashboard');
    
    // Tickets
    Route::get('/tickets/new', CreateWizard::class)->name('tickets.create');
});

// Logout (POST)
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->middleware('auth')->name('logout');

// Fallback: qualquer rota não mapeada volta para a raiz (que decide login/dashboard)
Route::fallback(fn () => redirect()->route('root'));
