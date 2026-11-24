<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Regola di validazione per verificare che un ID appartenga a un utente attivo con il ruolo specificato.
 */
class ActiveUserWithRole implements ValidationRule
{
    /**
     * Inizializza la regola specificando il ruolo richiesto.
     */
    public function __construct(private string $role) {}

    /**
     * Esegue la validazione verificando l'esistenza e lo stato dell'utente nel database.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! User::role($this->role)->active()->whereKey($value)->exists()) {
            $fail("L'utente selezionato non è un {$this->role} attivo.");
        }
    }
}
