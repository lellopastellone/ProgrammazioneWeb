<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reindirizza a /setup qualunque richiesta web finché non esiste un organizer nel sistema.
 */
class EnsureSetupIsComplete
{
    /**
     * Intercetta la richiesta e verifica la presenza dell'organizer reindirizzando se assente.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Controlla direttamente sul database a ogni richiesta: non usiamo la cache per evitare disallineamenti se il database viene resettato (migrate:fresh).
        if (! $request->routeIs('setup', 'setup.store') && ! User::hasOrganizer()) {
            return to_route('setup');
        }

        return $next($request);
    }
}
