<?php

namespace App\Rules;

use App\Models\EmailDomain;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Valida che l'indirizzo email appartenga a uno dei domini aziendali ammessi.
 * Consente l'email corrente senza modifiche e non applica vincoli se non vi sono domini configurati.
 */
class CompanyEmail implements ValidationRule
{
    private Collection $domains;

    /**
     * Inizializza la regola caricando i domini ammessi e l'eventuale email attuale.
     */
    public function __construct(?iterable $domains = null, private ?string $current = null)
    {
        $this->domains = collect($domains ?? EmailDomain::orderBy('id')->pluck('domain'));
    }

    /**
     * Esegue la validazione verificando che il dominio dell'email sia tra quelli autorizzati.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->domains->isEmpty() || $value === $this->current) {
            return;
        }

        if (! $this->domains->contains(Str::lower(Str::afterLast((string) $value, '@')))) {
            $fail('Usa un indirizzo email aziendale ('.$this->domains->map(fn (string $d) => "@{$d}")->join(', ').').');
        }
    }
}
