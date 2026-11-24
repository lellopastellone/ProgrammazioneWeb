<?php

namespace App\Http\Requests;

use App\Actions\Fortify\PasswordValidationRules;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida l'impostazione della prima password tramite token di invito.
 */
class SetPasswordRequest extends FormRequest
{
    use PasswordValidationRules;

    /**
     * Definisce le regole di validazione per token, email e requisiti della password.
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => $this->passwordRules(),
        ];
    }
}
