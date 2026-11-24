<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rifiuta con 400 l'input (chiavi e valori) con byte UTF-8 non validi o con il carattere NUL: nessun browser li manda
 * da un form. PostgreSQL rifiuta i primi con un 500 e tronca le stringhe al NUL (aggirando UniqueIgnoringCase), e un
 * old input così non si serializza in JSON: la sessione si svuoterebbe e l'utente si ritroverebbe sloggato.
 */
class RejectMalformedInput
{
    public function handle(Request $request, Closure $next): Response
    {
        $malformed = fn ($text) => is_string($text) && (! mb_check_encoding($text, 'UTF-8') || str_contains($text, "\0"));

        foreach (Arr::dot($request->input()) as $key => $value) {
            abort_if($malformed((string) $key) || $malformed($value), 400);
        }

        return $next($request);
    }
}
