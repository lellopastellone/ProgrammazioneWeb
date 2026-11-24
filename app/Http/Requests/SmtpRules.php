<?php

namespace App\Http\Requests;

use App\Models\MailSetting;
use App\Rules\CompanyEmail;
use Illuminate\Validation\Rule;

/**
 * Trait condiviso che definisce le regole di validazione e normalizzazione per i campi SMTP.
 */
trait SmtpRules
{
    /**
     * Restituisce le regole di validazione applicate ai parametri di connessione SMTP.
     */
    protected function smtpRules(?iterable $domains = null): array
    {
        $saved = MailSetting::first();

        return [
            'smtp_host' => ['required', 'string', 'max:255'],
            // Solo porte SMTP: l'app non deve diventare uno strumento per sondare altri servizi della rete.
            'smtp_port' => ['required', 'integer', Rule::in([25, 465, 587, 1025, 2525])],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            // Se cambia il server serve di nuovo la password: quella salvata non parte mai verso un host nuovo.
            'smtp_password' => [Rule::requiredIf(fn () => filled($saved?->password) && $this->input('smtp_host') !== $saved->host), 'nullable', 'string', 'max:255'],
            'smtp_from_address' => ['required', 'email', 'max:255', new CompanyEmail($domains, $saved?->from_address)],
        ];
    }

    /**
     * Messaggi in italiano per le regole SMTP.
     */
    public function messages(): array
    {
        return [
            'smtp_port.in' => 'Porta SMTP non valida: usa 25, 465, 587, 1025 o 2525.',
            'smtp_password.required' => 'Il server è cambiato: inserisci la password SMTP.',
        ];
    }

    /**
     * Converte i campi del form nei parametri del modello MailSetting, preservando la password se vuota.
     */
    public function smtp(): array
    {
        $data = collect($this->safe()->only(['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_from_address']))
            ->mapWithKeys(fn ($value, string $key) => [substr($key, strlen('smtp_')) => $value]);

        return $data->reject(fn ($value, string $key) => $key === 'password' && blank($value))->all();
    }
}
