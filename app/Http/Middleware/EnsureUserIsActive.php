<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Disconnette forzatamente e invalida la sessione di utenti disattivati durante la navigazione.
 */
class EnsureUserIsActive
{
    /**
     * Intercetta la richiesta e termina la sessione se l'utente risulta disattivato.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return to_route('login')->withErrors(['email' => 'Account disattivato.']);
        }

        return $next($request);
    }
}
