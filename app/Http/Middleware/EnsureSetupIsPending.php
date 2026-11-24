<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Limita l'accesso alla configurazione iniziale solo finché non è presente un organizer.
 */
class EnsureSetupIsPending
{
    /**
     * Intercetta la richiesta e restituisce errore 404 se l'organizer è già configurato.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_if(User::hasOrganizer(), 404);

        return $next($request);
    }
}
