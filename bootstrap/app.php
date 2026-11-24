<?php

/**
 * Configurazione e inizializzazione dell'istanza principale dell'applicazione Laravel
 * (definizione routing, middleware globali di sicurezza/setup, alias Spatie e gestione eccezioni).
 */

use App\Http\Middleware\EnsureSetupIsComplete;
use App\Http\Middleware\EnsureSetupIsPending;
use App\Http\Middleware\EnsureTwoFactorIsConfirmed;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\RejectMalformedInput;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\FrameGuard;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // EnsureSetupIsComplete in testa al gruppo: deve scattare prima di auth, che altrimenti rimanderebbe gli ospiti a /login.
        // EnsureUserIsActive su tutto il gruppo web, non solo sulle route dell'app: anche profilo Jetstream, route di
        // Fortify e /livewire/update. FrameGuard (X-Frame-Options: SAMEORIGIN): niente pagine dentro iframe altrui (clickjacking).
        // RejectMalformedInput (400 su UTF-8 non valido o NUL) per ultimo, così anche la sua risposta passa dai due precedenti.
        $middleware->web(prepend: EnsureSetupIsComplete::class, append: [EnsureUserIsActive::class, FrameGuard::class, RejectMalformedInput::class]);

        $middleware->alias([
            'setup.pending' => EnsureSetupIsPending::class,
            'two-factor.verified' => EnsureTwoFactorIsConfirmed::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Restituisce sempre errori in formato JSON per le rotte API (/api/*), evitando pagine HTML.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
