<?php

namespace App\Http\Requests;

use App\Rules\ActiveUserWithRole;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida l'assegnazione o modifica del Project Manager responsabile del progetto.
 */
class UpdateProjectManagerRequest extends FormRequest
{
    /**
     * Definisce le regole di validazione verificando che l'utente sia un PM attivo.
     */
    public function rules(): array
    {
        return [
            'pm_id' => ['bail', 'required', 'integer', new ActiveUserWithRole('project_manager')],
        ];
    }
}
