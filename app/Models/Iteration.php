<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Rappresenta un'iterazione temporale (sprint) associata a un progetto software.
 */
class Iteration extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'starts_on', 'ends_on'];

    /**
     * Definisce il casting delle date di inizio e fine dell'iterazione.
     */
    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    /**
     * Definisce la relazione di appartenenza al progetto di riferimento.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Definisce la relazione con i task associati a questa iterazione.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Verifica se la data specificata è compresa nel periodo dell'iterazione.
     */
    public function includes(CarbonInterface $date): bool
    {
        return $date->between($this->starts_on, $this->ends_on);
    }
}
