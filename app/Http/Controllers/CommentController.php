<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Gestisce l'elenco, la creazione e la rimozione dei commenti associati ai task.
 */
class CommentController extends Controller
{
    /**
     * Mostra l'elenco dei commenti associati a un determinato task.
     */
    public function index(Task $task): View
    {
        $comments = $task->comments()->with('author')->get();

        return view('comments.index', compact('task', 'comments'));
    }

    /**
     * Salva un nuovo commento per il task associandolo all'utente autenticato.
     */
    public function store(StoreCommentRequest $request, Task $task): RedirectResponse
    {
        $comment = new Comment($request->validated());
        $comment->created_by = $request->user()->id;
        $task->comments()->save($comment);

        return back();
    }

    /**
     * Elimina il commento specificato dal database.
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return back();
    }
}
