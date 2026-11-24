<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Rappresenta un team o reparto tecnico aziendale composto da membri operativi.
 */
class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    /**
     * Definisce la relazione molti-a-molti con gli utenti assegnati al team.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
