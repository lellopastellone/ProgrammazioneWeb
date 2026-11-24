<?php

namespace App\Enums;

/**
 * Definisce i livelli di priorità assegnabili a un task.
 */
enum Priority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';

    /**
     * Restituisce l'etichetta testuale leggibile associata alla priorità.
     */
    public function label(): string
    {
        return match ($this) {
            self::Low => 'Bassa',
            self::Medium => 'Media',
            self::High => 'Alta',
            self::Critical => 'Critica',
        };
    }

    /**
     * Restituisce il nome della classe colore Bootstrap per il badge della priorità.
     */
    public function color(): string
    {
        return match ($this) {
            self::Low      => 'secondary',
            self::Medium   => 'warning',
            self::High     => 'danger',
            self::Critical => 'dark',
        };
    }
}
