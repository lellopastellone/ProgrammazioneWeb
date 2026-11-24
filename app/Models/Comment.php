<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Rappresenta un commento lasciato da un utente all'interno di un task.
 */
class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['text'];

    /**
     * Definisce la relazione di appartenenza al task commentato.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Definisce la relazione con l'autore del commento.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
