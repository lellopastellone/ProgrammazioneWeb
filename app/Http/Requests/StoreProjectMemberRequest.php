<?php

namespace App\Http\Requests;

use App\Rules\ActiveUserWithRole;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida l'aggiunta di un membro operativo al gruppo di lavoro di un progetto.
 */
class StoreProjectMemberRequest extends FormRequest
{
    /**
     * Definisce le regole di validazione verificando che l'utente sia un member attivo.
     */
    public function rules(): array
    {
        return [
            'user_id' => ['bail', 'required', 'integer', new ActiveUserWithRole('member')],
        ];
    }
}
