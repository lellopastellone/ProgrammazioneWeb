<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Rules\CompanyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

/**
 * Gestisce la validazione e l'aggiornamento dei dati anagrafici del profilo utente.
 */
class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Valida e salva le informazioni del profilo, inclusi foto ed email aziendale.
     */
    public function update(User $user, array $input): void
    {
        // In minuscolo prima di unique: su PostgreSQL il confronto distingue le maiuscole e Fortify cerca l'email in minuscolo.
        if (is_string($input['email'] ?? null)) {
            $input['email'] = Str::lower($input['email']);
        }

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id), new CompanyEmail(current: $user->email)],
            // Cambiare email richiede la password attuale: con una sessione rubata non si dirotta il recupero password.
            'current_password' => [Rule::requiredIf(($input['email'] ?? null) !== $user->email), 'current_password:web'],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'current_password.required' => 'Per cambiare email inserisci la password attuale.',
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'surname' => $input['surname'],
                'email' => $input['email'],
            ])->save();
        }

        session()->flash('profile_updated', 'Informazioni del profilo modificate correttamente.');
    }

    /**
     * Aggiorna i dati dell'utente azzerando la verifica email e reinviando la notifica.
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'surname' => $input['surname'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
