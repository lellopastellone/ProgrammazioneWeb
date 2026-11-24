<?php

namespace App\Enums;

/**
 * Rappresenta gli stati del flusso di lavoro (workflow) di un task.
 */
enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case InReview = 'in_review';
    case Completed = 'completed';

    /**
     * Restituisce l'etichetta leggibile associata allo stato del task.
     */
    public function label(): string
    {
        return match ($this) {
            self::Todo => 'Da fare',
            self::InProgress => 'In corso',
            self::InReview => 'In review',
            self::Completed => 'Completato',
        };
    }

    /**
     * Restituisce il nome della classe colore Bootstrap per lo stato.
     */
    public function color(): string
    {
        return match ($this) {
            self::Todo       => 'secondary',
            self::InProgress => 'primary',
            self::InReview   => 'warning',
            self::Completed  => 'success',
        };
    }

    /**
     * Restituisce l'icona Bootstrap Icons corrispondente allo stato.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Todo       => 'bi-list-task',
            self::InProgress => 'bi-arrow-repeat',
            self::InReview   => 'bi-eye',
            self::Completed  => 'bi-check-circle',
        };
    }

    /**
     * Valida la transizione di stato: gli assegnatari avanzano fino alla review, il PM gestisce la review.
     */
    public function canTransitionTo(self $next, bool $isPm): bool
    {
        return match ([$this, $next]) {
            [self::Todo, self::InProgress],
            [self::InProgress, self::InReview] => ! $isPm,
            [self::InReview, self::Completed],
            [self::InReview, self::InProgress] => $isPm,
            default => false,
        };
    }
}
