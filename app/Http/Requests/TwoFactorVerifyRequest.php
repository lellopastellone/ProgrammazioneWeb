<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Fortify;

/**
 * Valida il codice 2FA (app o codice di recupero) richiesto da EnsureTwoFactorIsConfirmed,
 * con la stessa logica della challenge di login di Fortify.
 */
class TwoFactorVerifyRequest extends FormRequest
{
    /**
     * Serve il codice dell'app oppure un codice di recupero.
     */
    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'required_without:recovery_code'],
            'recovery_code' => ['nullable', 'string'],
        ];
    }

    /**
     * Messaggi in italiano.
     */
    public function messages(): array
    {
        return ['code.required_without' => 'Inserisci il codice a 6 cifre o un codice di recupero.'];
    }

    /**
     * Rifiuta un codice che non corrisponde. hasValidCode() si chiama una volta sola: Fortify segna il codice come usato.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isEmpty() && ! $this->validRecoveryCode() && ! $this->hasValidCode()) {
                    $validator->errors()->add($this->filled('recovery_code') ? 'recovery_code' : 'code', 'Codice non valido.');
                }
            },
        ];
    }

    /**
     * Il codice dell'app è corretto (solo per chi ha la 2FA attiva: senza secret non c'è niente da verificare).
     */
    private function hasValidCode(): bool
    {
        $user = $this->user();

        return $this->filled('code') && $user->hasEnabledTwoFactorAuthentication()
            && app(TwoFactorAuthenticationProvider::class)->verify(Fortify::currentEncrypter()->decrypt($user->two_factor_secret), $this->input('code'));
    }

    /**
     * Il codice di recupero inserito, se è tra quelli dell'utente (confronto a tempo costante).
     */
    public function validRecoveryCode(): ?string
    {
        $user = $this->user();

        if (! $this->filled('recovery_code') || ! $user->hasEnabledTwoFactorAuthentication()) {
            return null;
        }

        return collect($user->recoveryCodes())->first(fn (string $code) => hash_equals($code, (string) $this->input('recovery_code')));
    }
}
