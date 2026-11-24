<?php

namespace App\Http\Requests;

use App\Models\Team;
use App\Rules\UniqueIgnoringCase;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida la creazione e l'aggiornamento di un team aziendale.
 */
class TeamRequest extends FormRequest
{
    /**
     * Definisce le regole di validazione per nome univoco e descrizione del team.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', new UniqueIgnoringCase(Team::query(), 'name', $this->route('team'), 'Esiste già un team con questo nome.')],
            'description' => ['nullable', 'string'],
        ];
    }
}
