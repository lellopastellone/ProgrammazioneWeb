<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Rules\UniqueIgnoringCase;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida l'aggiornamento delle informazioni generali (nome e descrizione) di un progetto.
 */
class UpdateProjectRequest extends FormRequest
{
    /**
     * Definisce le regole di validazione per nome (unico tra tutti i progetti) e descrizione.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', new UniqueIgnoringCase(Project::query(), 'name', $this->route('project'), 'Esiste già un progetto con questo nome.')],
            'description' => ['nullable', 'string'],
        ];
    }
}
