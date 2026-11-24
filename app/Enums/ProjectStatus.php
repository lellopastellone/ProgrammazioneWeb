<?php

namespace App\Enums;

/**
 * Rappresenta gli stati possibili del ciclo di vita di un progetto.
 */
enum ProjectStatus: string
{
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Finished = 'finished';
    case Archived = 'archived';

    /**
     * Restituisce gli stati con lavoro aperto (pianificato e in corso).
     */
    public static function active(): array
    {
        return [self::Planned, self::InProgress];
    }

    /**
     * Restituisce l'etichetta leggibile associata allo stato del progetto.
     */
    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Pianificato',
            self::InProgress => 'In corso',
            self::Finished => 'Concluso',
            self::Archived => 'Archiviato',
        };
    }

    /**
     * Restituisce il nome della classe colore Bootstrap per lo stato.
     */
    public function color(): string
    {
        return match ($this) {
            self::Planned     => 'secondary',
            self::InProgress  => 'primary',
            self::Finished    => 'success',
            self::Archived    => 'neutral',
        };
    }
}
