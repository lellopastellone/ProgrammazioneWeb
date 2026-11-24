<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

/**
 * Definisce le regole di autorizzazione per la visualizzazione e gestione dei progetti.
 */
class ProjectPolicy
{
    /**
     * Determina se l'utente può visualizzare i dettagli e i contenuti del progetto.
     */
    public function view(User $user, Project $project): bool
    {
        return $user->can('projects.view-all')
            || $project->isManagedBy($user)
            || $project->hasMember($user);
    }

    /**
     * Determina se l'utente può gestire le impostazioni e i membri del progetto in quanto PM assegnato.
     */
    public function manage(User $user, Project $project): bool
    {
        return $user->can('projects.manage') && $project->isManagedBy($user);
    }

    /**
     * Determina se l'utente può aggiornare i dati di un progetto attivo e modificabile.
     */
    public function update(User $user, Project $project): bool
    {
        return $this->manage($user, $project) && $project->isEditable();
    }

    /**
     * Determina se l'utente può cambiare il Project Manager: solo sui progetti ancora modificabili.
     */
    public function assignManager(User $user, Project $project): bool
    {
        return $user->can('projects.assign-manager') && $project->isEditable();
    }

    /**
     * Determina se l'utente può cambiare lo stato del progetto: il suo PM, oppure l'organizer quando
     * il progetto è rimasto senza un PM attivo (in quel caso può solo archiviarlo o riattivarlo, vedi UpdateProjectStatusRequest).
     */
    public function changeStatus(User $user, Project $project): bool
    {
        return $this->manage($user, $project) || $this->manageOrphan($user, $project);
    }

    /**
     * Determina se l'organizer può intervenire su un progetto senza un PM attivo (mai assegnato o
     * disattivato): altrimenti nessuno potrebbe più archiviarlo.
     */
    public function manageOrphan(User $user, Project $project): bool
    {
        return $user->can('projects.create') && ! $project->hasActiveManager();
    }

    /**
     * Determina se l'utente può eliminare il progetto: solo chi crea i progetti e solo se non ha mai
     * avuto un PM (pm_id, una volta assegnato, non torna più nullo).
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->can('projects.create') && $project->pm_id === null;
    }
}
