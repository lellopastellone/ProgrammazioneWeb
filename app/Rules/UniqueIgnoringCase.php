<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Verifica l'unicità di un valore tra record correlati ignorando la differenza tra maiuscole e minuscole.
 */
class UniqueIgnoringCase implements ValidationRule
{
    /**
     * Inizializza la regola specificando i record da confrontare (relazione o query), la colonna,
     * l'eventuale modello da ignorare e il messaggio.
     */
    public function __construct(
        private Builder|Relation $siblings,
        private string $column,
        private ?Model $ignore,
        private string $message,
    ) {}

    /**
     * Esegue la validazione verificando che il valore non sia già presente tra i record fratelli.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Confronto in PHP (portabile tra SQLite e PostgreSQL senza query SQL grezze).
        $taken = $this->siblings
            ->when($this->ignore, fn ($q) => $q->whereKeyNot($this->ignore->getKey()))
            ->pluck($this->column)
            ->contains(fn (string $existing) => mb_strtolower($existing) === mb_strtolower((string) $value));

        if ($taken) {
            $fail($this->message);
        }
    }
}
