<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Rappresenta un progetto software tracciandone dettagli, iterazioni, task e membri assegnati.
 */
class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'status', 'pm_id'];

    /**
     * Definisce il casting dello stato del progetto sull'enum ProjectStatus.
     */
    protected function casts(): array
    {
        return ['status' => ProjectStatus::class];
    }

    /**
     * Definisce la relazione con l'utente (organizer) che ha creato il progetto.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Definisce la relazione con il Project Manager assegnato alla guida del progetto.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pm_id');
    }

    /**
     * Definisce la relazione molti-a-molti con i membri del gruppo di progetto (ProjectGroup).
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Definisce la relazione con le iterazioni (sprint) temporali del progetto.
     */
    public function iterations(): HasMany
    {
        return $this->hasMany(Iteration::class)->orderBy('starts_on');
    }

    /**
     * Definisce la relazione con tutti i task appartenenti al progetto.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Verifica se l'utente specificato è il Project Manager responsabile del progetto.
     */
    public function isManagedBy(User $user): bool
    {
        return $this->pm_id === $user->id;
    }

    /**
     * Verifica se il progetto ha un Project Manager attivo (assegnato e non disattivato).
     */
    public function hasActiveManager(): bool
    {
        return (bool) $this->manager?->active;
    }

    /**
     * Verifica se l'utente appartiene al gruppo di lavoro assegnato al progetto.
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->whereKey($user->id)->exists();
    }

    /**
     * Determina se il progetto è in uno stato attivo che ne consente la modifica.
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ProjectStatus::active(), true);
    }

    /**
     * Verifica se il progetto si trova nello stato archiviato.
     */
    public function isArchived(): bool
    {
        return $this->status === ProjectStatus::Archived;
    }

    /**
     * Verifica se il progetto si trova nello stato concluso.
     */
    public function isFinished(): bool
    {
        return $this->status === ProjectStatus::Finished;
    }

    /**
     * Filtra la query limitando i progetti a quelli visibili all'utente specificato.
     */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if ($user->can('projects.view-all')) {
            return;
        }

        $query->where(fn (Builder $q) => $q
            ->where('pm_id', $user->id)
            ->orWhereHas('members', fn (Builder $m) => $m->whereKey($user->id)));
    }

    /**
     * Filtra la query includendo solo i progetti con stato attivo (pianificato o in corso).
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereIn('status', ProjectStatus::active());
    }
}
