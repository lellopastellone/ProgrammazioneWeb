<?php

namespace App\Http\Requests;

use App\Rules\CompanyEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Valida i dati per la creazione e l'aggiornamento degli utenti da parte dell'organizer.
 */
class UserRequest extends FormRequest
{
    /**
     * Definisce le regole di validazione per anagrafica, email aziendale, ruolo e team.
     */
    public function rules(): array
    {
        $creating = $this->route('user') === null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->route('user')),
                new CompanyEmail(current: $this->route('user')?->email),
            ],
            'role' => $creating ? ['required', 'string', Rule::in(['organizer', 'project_manager', 'member'])] : ['prohibited'],
            'teams' => [$this->isMember() ? 'array' : 'prohibited'],
            'teams.*' => ['bail', 'integer', Rule::exists('teams', 'id')],
        ];
    }

    /**
     * Email in minuscolo prima di validarla: unique su PostgreSQL distingue le maiuscole e Fortify cerca il login in minuscolo.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => Str::lower($this->input('email'))]);
        }
    }

    /**
     * Personalizza i messaggi di errore per i vincoli sui ruoli associati ai team.
     */
    public function messages(): array
    {
        return ['teams.prohibited' => 'Solo i member possono far parte di un team.'];
    }

    /**
     * Determina se l'utente in creazione o modifica possiede il ruolo di member.
     */
    private function isMember(): bool
    {
        return $this->route('user')?->hasRole('member') ?? $this->input('role') === 'member';
    }
}
