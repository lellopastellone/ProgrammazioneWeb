<?php

/**
 * Rotte di amministrazione riservate all'organizer (e parzialmente consultabili dai Project Manager):
 * gestione anagrafica utenti aziendali, team tecnici e configurazione del server SMTP.
 */

use App\Http\Controllers\MailSettingController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Controllers\TwoFactorVerifyController;
use App\Http\Controllers\UserActiveController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Elenchi e schede di consultazione leggibili anche dal PM per comporre i gruppi di progetto (email mascherate).
Route::middleware('role:organizer|project_manager')->group(function () {
    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
});

Route::middleware('role:organizer')->group(function () {
    Route::resource('teams', TeamController::class)->except(['index', 'show']);
    Route::post('/teams/{team}/members', [TeamMemberController::class, 'store'])->name('teams.members.store');
    Route::delete('/teams/{team}/members/{user}', [TeamMemberController::class, 'destroy'])->name('teams.members.destroy');

    // Gli utenti non si cancellano: si disattivano. Gli organizer non modificano altri organizer (UserPolicy).
    // Modifica utente e server SMTP chiedono di nuovo la password (password.confirm) e, a chi ha la 2FA attiva,
    // anche il codice (two-factor.verified): con l'email o l'SMTP si dirotta il recupero password, quindi una
    // sessione rubata non deve bastare.
    Route::resource('users', UserController::class)
        ->except(['index', 'destroy'])
        ->middlewareFor('edit', ['can:edit,user', 'password.confirm', 'two-factor.verified'])
        ->middlewareFor('update', ['can:manage,user', 'password.confirm', 'two-factor.verified']);
    Route::patch('/users/{user}/active', [UserActiveController::class, 'update'])
        ->can('manage', 'user')
        ->name('users.active.update');

    Route::get('/settings/mail', [MailSettingController::class, 'edit'])->middleware(['password.confirm', 'two-factor.verified'])->name('mail-settings.edit');
    Route::put('/settings/mail', [MailSettingController::class, 'update'])->middleware(['password.confirm', 'two-factor.verified'])->name('mail-settings.update');

    // Verifica del codice 2FA richiesta da two-factor.verified. Massimo 5 tentativi al minuto per utente (codici a 6 cifre).
    Route::get('/user/verify-two-factor', [TwoFactorVerifyController::class, 'create'])->name('two-factor.verify');
    Route::post('/user/verify-two-factor', [TwoFactorVerifyController::class, 'store'])->middleware('throttle:5,1')->name('two-factor.verify.store');
});
