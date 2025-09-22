<?php

use App\Livewire\Auth\Login;
use App\Livewire\Home\Index;
use App\Livewire\Admin\Audit as AdminAudit;
use App\Livewire\Admin\Organization as AdminOrganization;
use App\Livewire\Admin\Overview as AdminOverview;
use App\Livewire\Admin\Roles as AdminRoles;
use App\Livewire\Admin\Settings as AdminSettings;
use App\Livewire\Admin\Slas as AdminSlas;
use App\Livewire\Admin\Workflows as AdminWorkflows;
use App\Livewire\Tickets\Admin as TicketsAdmin;
use App\Livewire\Tickets\CreateWizard;
use App\Livewire\Tickets\History as TicketsHistory;
use App\Livewire\Tickets\Index as TicketsIndex;
use App\Livewire\Tickets\Show as TicketShow;
use App\Http\Controllers\Auth\SicodeSsoController;
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

    Route::post('/sicode/auto-login', SicodeSsoController::class)
        ->name('sicode.auto-login');
});

// Authenticated
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Index::class)->name('dashboard');
    Route::get('/tickets', TicketsIndex::class)->name('tickets.index');    // Tickets
    Route::get('/tickets/new', CreateWizard::class)->name('tickets.create');
    Route::get('/tickets/admin', TicketsAdmin::class)->name('tickets.admin');
    Route::get('/tickets/history', TicketsHistory::class)->name('tickets.history');
    Route::get('/tickets/{ticket}', TicketShow::class)->name('tickets.show');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/overview', AdminOverview::class)->name('overview');
        Route::get('/settings', AdminSettings::class)->name('settings');
        Route::get('/organization', AdminOrganization::class)->name('organization');
        Route::get('/slas', AdminSlas::class)->name('slas');
        Route::get('/workflows', AdminWorkflows::class)->name('workflows');
        Route::get('/audit', AdminAudit::class)->name('audit');
        Route::get('/roles', AdminRoles::class)->name('roles');
    });
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
