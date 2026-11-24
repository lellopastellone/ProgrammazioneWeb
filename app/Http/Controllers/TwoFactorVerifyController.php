<?php

namespace App\Http\Controllers;

use App\Http\Requests\TwoFactorVerifyRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Conferma con il codice 2FA prima delle pagine protette da EnsureTwoFactorIsConfirmed (modifica utenti, server SMTP).
 */
class TwoFactorVerifyController extends Controller
{
    /**
     * Mostra il form del codice: la stessa view della challenge di login, con l'action della verifica.
     */
    public function create(): View
    {
        return view('auth.two-factor-challenge', ['verify' => true]);
    }

    /**
     * Registra la conferma in sessione (consumando l'eventuale codice di recupero) e torna alla pagina richiesta.
     */
    public function store(TwoFactorVerifyRequest $request): RedirectResponse
    {
        if ($code = $request->validRecoveryCode()) {
            $request->user()->replaceRecoveryCode($code);
        }

        $request->session()->put('auth.two_factor_confirmed_at', time());

        return redirect()->intended(route('dashboard'));
    }
}
