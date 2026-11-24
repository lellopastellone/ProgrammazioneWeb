<?php

namespace App\Policies;

use App\Models\Iteration;
use App\Models\User;

/**
 * Gestisce l'autorizzazione per le operazioni sulle iterazioni (sprint) di progetto.
 */
class IterationPolicy
{
    /**
     * Determina se l'utente può gestire (modificare o cancellare) l'iterazione.
     * Richiede che il progetto correlato sia modificabile e gestito dall'utente.
     */
    public function manage(User $user, Iteration $iteration): bool
    {
        return $iteration->project->isEditable() && $user->can('manage', $iteration->project);
    }
}
