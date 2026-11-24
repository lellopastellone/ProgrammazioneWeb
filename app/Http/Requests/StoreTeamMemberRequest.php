<?php

namespace App\Http\Requests;

use App\Rules\ActiveUserWithRole;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida l'inserimento di un utente all'interno di un team aziendale.
 */
class StoreTeamMemberRequest extends FormRequest
{
    /**
     * Ammette solo utenti attivi con ruolo member.
     */
    public function rules(): array
    {
        return [
            'user_id' => ['bail', 'required', 'integer', new ActiveUserWithRole('member')],
        ];
    }
}
