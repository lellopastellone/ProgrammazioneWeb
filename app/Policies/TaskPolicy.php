<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

/**
 * Definisce le regole di autorizzazione per la visualizzazione e modifica dei task.
 */
class TaskPolicy
{
    /**
     * Determina se l'utente può visualizzare il task in base ai permessi sul progetto.
     */
    public function view(User $user, Task $task): bool
    {
        return $user->can('view', $task->project);
    }

    /**
     * Determina se l'utente può creare, modificare o eliminare il task nel progetto.
     */
    public function manage(User $user, Task $task): bool
    {
        return $task->project->isEditable() && $user->can('manage', $task->project);
    }

    /**
     * Determina se l'utente (assegnatario o PM) può avanzare o modificare lo stato del task.
     * Serve anche vedere il task: un assegnatario rimasto fuori dal progetto non lo muove più.
     */
    public function updateStatus(User $user, Task $task): bool
    {
        return $task->project->isEditable()
            && $this->view($user, $task)
            && (($user->can('tasks.update-status') && $task->isAssignedTo($user)) || $this->manage($user, $task));
    }

    /**
     * Determina se l'utente può commentare il task (escluso l'organizer che è in sola lettura).
     */
    public function comment(User $user, Task $task): bool
    {
        return $task->project->isEditable()
            && $this->view($user, $task)
            && (($user->can('comments.create') && $task->project->hasMember($user)) || $user->can('manage', $task->project));
    }
}
