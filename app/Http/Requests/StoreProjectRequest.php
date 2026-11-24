<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Rules\ActiveUserWithRole;
use App\Rules\UniqueIgnoringCase;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida i dati per la creazione di un nuovo progetto e l'eventuale assegnazione del PM.
 */
class StoreProjectRequest extends FormRequest
{
    /**
     * Definisce le regole di validazione per nome (unico tra tutti i progetti), descrizione e Project Manager.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', new UniqueIgnoringCase(Project::query(), 'name', null, 'Esiste già un progetto con questo nome.')],
            'description' => ['nullable', 'string'],
            'pm_id' => ['bail', 'nullable', 'integer', new ActiveUserWithRole('project_manager')],
        ];
    }
}
