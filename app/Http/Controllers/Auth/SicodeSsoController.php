<?php

namespace App\Http\Controllers\Auth;

use App\Listeners\SyncSicodeUserOnLogin;
use App\Models\SicodeUser;
use Illuminate\Auth\Events\Login as AuthLoginEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class SicodeSsoController extends Controller
{
    /**
     * Endpoint de SSO que autentica um usuário do SICODE utilizando o token.
     *
     * Espera receber via POST:
     * - token (string obrigatória)
     * - uuid (opcional) ou email (opcional): usados para reforçar a busca do usuário
     * - redirect_to (opcional): rota para redirecionar após login
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'uuid' => ['nullable', 'string'],
            'email' => ['nullable', 'string', 'email'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $query = SicodeUser::query()->where('token', $data['token']);

        if (!empty($data['uuid'])) {
            $query->where('id', $data['uuid']);
        }

        if (!empty($data['email'])) {
            $query->where('email', $data['email']);
        }

        $sicodeUser = $query->firstOrFail();

        Auth::login($sicodeUser, remember: true);

        app(SyncSicodeUserOnLogin::class)->handle(new AuthLoginEvent('web', $sicodeUser, true));

        $redirect = $data['redirect_to'] ?? route('dashboard');

        return redirect()->to($redirect);
    }
}
