<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;

/**
 * Gestisce l'attivazione e la disattivazione dello stato operativo di un utente.
 */
class UserActiveController extends Controller
{
    /**
     * Inverte lo stato attivo dell'utente senza cancellarne i dati e le relazioni.
     */
    public function update(User $user): RedirectResponse
    {
        $user->update(['active' => ! $user->active]);

        return back();
    }
}
