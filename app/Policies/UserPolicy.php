<?php

namespace App\Policies;

use App\Models\User;

/**
 * Definisce le regole di autorizzazione per la gestione e modifica degli utenti.
 */
class UserPolicy
{
    /**
     * Determina se l'utente può gestire o attivare/disattivare l'account target (esclusi gli organizer).
     */
    public function manage(User $user, User $target): bool
    {
        return $user->can('users.manage') && ! $target->hasRole('organizer');
    }

    /**
     * Determina se l'utente può accedere al form di modifica (gestione ammessa o profilo personale).
     */
    public function edit(User $user, User $target): bool
    {
        return $user->is($target) || $this->manage($user, $target);
    }
}
