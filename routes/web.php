<?php

use App\Livewire\Auth\Login;
use App\Livewire\Home\Index;
use App\Livewire\Tickets\Admin as TicketsAdmin;
use App\Livewire\Tickets\CreateWizard;
use App\Livewire\Tickets\History as TicketsHistory;
use App\Livewire\Tickets\Index as TicketsIndex;
use App\Livewire\Tickets\Show as TicketShow;
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
    Route::get('/tickets', TicketsIndex::class)->name('tickets.index');    // Tickets
    Route::get('/tickets/new', CreateWizard::class)->name('tickets.create');
    Route::get('/tickets/admin', TicketsAdmin::class)->name('tickets.admin');
    Route::get('/tickets/history', TicketsHistory::class)->name('tickets.history');
    Route::get('/tickets/{ticket}', TicketShow::class)->name('tickets.show');
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
