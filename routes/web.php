<?php

/**
 * File di routing web principale: definizione vincoli globali sui parametri URL, route pubbliche
 * (landing page, wizard di primo setup, impostazione password iniziale) e inclusione dei moduli protetti.
 */

use App\Http\Controllers\SetPasswordController;
use App\Http\Controllers\SetupController;
use Illuminate\Support\Facades\Route;

// Id solo numerici (massimo 18 cifre, entra in un bigint): "/projects/abc" è un 404, non un errore SQL su PostgreSQL.
Route::patterns(array_fill_keys(['project', 'task', 'iteration', 'comment', 'user', 'team'], '[0-9]{1,18}'));

Route::get('/', function () {
    return view('welcome');
});

// Configurazione iniziale: raggiungibile solo finché non esiste un organizer (EnsureSetupIsComplete rimanda qui tutto il resto).
Route::middleware(['guest', 'setup.pending'])->group(function () {
    Route::get('/setup', [SetupController::class, 'create'])->name('setup');
    Route::post('/setup', [SetupController::class, 'store'])->name('setup.store');
});

// Prima password di un utente creato dall'organizer (link della mail di benvenuto). Il recupero resta su /reset-password di Fortify.
// throttle:password-reset limita il POST per IP (FortifyServiceProvider).
Route::middleware(['guest', 'throttle:password-reset'])->group(function () {
    Route::get('/set-password/{token}', [SetPasswordController::class, 'create'])->name('password.set');
    Route::post('/set-password', [SetPasswordController::class, 'store'])->name('password.set.store');
});

// Gli utenti disattivati li chiude EnsureUserIsActive, registrato sull'intero gruppo web (bootstrap/app.php).
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
])->group(function () {
    require __DIR__.'/dashboard.php';
    require __DIR__.'/projects.php';
    require __DIR__.'/tasks.php';
    require __DIR__.'/admin.php';
});
