<?php

namespace App\Http\Requests;

use App\Actions\Fortify\PasswordValidationRules;
use App\Rules\CompanyEmail;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Valida i dati della procedura di setup iniziale: organizer, domini aziendali e parametri SMTP.
 */
class SetupRequest extends FormRequest
{
    use PasswordValidationRules, SmtpRules;

    /**
     * Definisce le regole di validazione per anagrafica owner, domini e credenziali mail.
     */
    public function rules(): array
    {
        $invalid = $this->domains()->reject(fn (string $d) => preg_match('/^[a-z0-9-]+(\.[a-z0-9-]+)+$/', $d));
        // Email e mittente si controllano solo sui domini validi: quelli sbagliati hanno già il loro errore su "domains".
        $valid = $this->domains()->diff($invalid);

        return [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users'), new CompanyEmail($valid)],
            'domains' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) use ($invalid) {
                    if ($invalid->isNotEmpty()) {
                        $fail('Dominio non valido: '.$invalid->join(', ').'.');
                    }
                },
            ],
            'password' => $this->passwordRules(),
            ...$this->smtpRules($valid),
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
     * Estrae e normalizza i domini email inseriti separati da virgole o spazi.
     */
    public function domains(): Collection
    {
        return Str::of($this->string('domains'))->lower()->explode(',')
            ->flatMap(fn (string $part) => preg_split('/\s+/', $part))
            ->map(fn (string $d) => ltrim(trim($d), '@'))
            ->filter()
            ->unique()
            ->values();
    }
}
