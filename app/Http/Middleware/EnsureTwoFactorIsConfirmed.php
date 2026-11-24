<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Come password.confirm, ma con il codice 2FA: chi ha la verifica in due passaggi attiva deve inserire il codice
 * (o un codice di recupero) prima delle pagine più delicate. Vale quanto la conferma della password (auth.password_timeout).
 * Chi non ha la 2FA attiva passa: gli basta password.confirm.
 */
class EnsureTwoFactorIsConfirmed
{
    /**
     * Rimanda alla verifica del codice se la 2FA è attiva e la conferma è assente o scaduta.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $confirmedAt = $request->session()->get('auth.two_factor_confirmed_at', 0);

        if ($request->user()->hasEnabledTwoFactorAuthentication() && time() - $confirmedAt > config('auth.password_timeout')) {
            return redirect()->guest(route('two-factor.verify'));
        }

        return $next($request);
    }
}
