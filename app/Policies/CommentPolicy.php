<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

/**
 * Gestisce l'autorizzazione per le operazioni sui commenti dei task.
 */
class CommentPolicy
{
    /**
     * Determina se l'utente può eliminare il commento specificato.
     * L'operazione è permessa all'autore o a chi gestisce il progetto modificabile, purché veda ancora il task
     * (un member rimosso dal progetto non cancella più i suoi vecchi commenti).
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $comment->task->project->isEditable()
            && $user->can('view', $comment->task)
            && ($comment->created_by === $user->id || $user->can('manage', $comment->task->project));
    }
}
