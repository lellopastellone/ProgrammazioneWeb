<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\TaskStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Rappresenta un'attività (task) di progetto con stato, priorità, date e membri assegnati.
 */
class Task extends Model
{
    use HasFactory;

    // status e completed_at non sono fillable: si cambiano solo da TaskStatusController.
    protected $fillable = ['title', 'description', 'start_date', 'end_date', 'priority', 'iteration_id'];

    /**
     * Definisce il casting di stati, priorità e date del task.
     */
    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => Priority::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Definisce la relazione di appartenenza al progetto.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Definisce la relazione con l'eventuale iterazione (sprint) assegnata.
     */
    public function iteration(): BelongsTo
    {
        return $this->belongsTo(Iteration::class);
    }

    /**
     * Definisce la relazione molti-a-molti con i membri assegnati al task (UserTask).
     */
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('assigned_at');
    }

    /**
     * Definisce la relazione con i commenti inseriti nel task ordinati per data.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    /**
     * Verifica se il task è assegnato all'utente indicato sfruttando la relazione se già caricata.
     */
    public function isAssignedTo(User $user): bool
    {
        return $this->relationLoaded('assignees')
            ? $this->assignees->contains($user)
            : $this->assignees()->whereKey($user->id)->exists();
    }

    /**
     * Verifica se il task è in ritardo (non completato e con data di fine precedente a oggi).
     */
    public function isOverdue(): bool
    {
        return $this->status !== TaskStatus::Completed && $this->end_date !== null && $this->end_date->lt(today());
    }

    /**
     * Filtra la query per includere esclusivamente i task non completati con scadenza superata.
     */
    public function scopeOverdue(Builder $query): void
    {
        $query->where('status', '!=', TaskStatus::Completed)->where('end_date', '<', today());
    }

    /**
     * Filtra la query limitandola ai task assegnati all'utente specificato.
     */
    public function scopeAssignedTo(Builder $query, User|int $user): void
    {
        $query->whereHas('assignees', fn (Builder $q) => $q->whereKey($user));
    }

    /**
     * Filtra la query per includere i task il cui intervallo temporale interseca il mese indicato.
     */
    public function scopeInMonth(Builder $query, CarbonInterface $month): void
    {
        // Carbon e non stringhe Y-m-d: il cast "date" salva "Y-m-d H:i:s" (sqlite confronta stringhe).
        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();

        $query
            ->where(fn (Builder $q) => $q
                ->where('start_date', '<=', $to)
                ->orWhere(fn (Builder $q) => $q->whereNull('start_date')->where('end_date', '<=', $to)))
            ->where(fn (Builder $q) => $q
                ->where('end_date', '>=', $from)
                ->orWhere(fn (Builder $q) => $q->whereNull('end_date')->where('start_date', '>=', $from)));
    }
}
