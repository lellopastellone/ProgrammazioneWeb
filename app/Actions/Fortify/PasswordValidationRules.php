<?php

namespace App\Actions\Fortify;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Fornisce le regole di validazione standard utilizzate per le password.
 */
trait PasswordValidationRules
{
    /**
     * Restituisce le regole di validazione predefinite per la sicurezza della password.
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', Password::default(), 'confirmed'];
    }
}
