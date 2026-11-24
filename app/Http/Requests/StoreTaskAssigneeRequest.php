<?php

namespace App\Http\Requests;

use App\Rules\ActiveUserWithRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida l'assegnazione di un membro del progetto a un determinato task.
 */
class StoreTaskAssigneeRequest extends FormRequest
{
    /**
     * Definisce le regole verificando che l'utente appartenga al gruppo del progetto.
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'bail',
                'required',
                'integer',
                Rule::exists('project_user', 'user_id')->where('project_id', $this->route('task')->project_id),
                new ActiveUserWithRole('member'),
                // Un secondo invio non deve riscrivere assigned_at (syncWithoutDetaching aggiorna la pivot esistente).
                Rule::unique('task_user', 'user_id')->where('task_id', $this->route('task')->id),
            ],
        ];
    }

    public function messages(): array
    {
        return ['user_id.unique' => 'Questo membro è già assegnato al task.'];
    }
}
