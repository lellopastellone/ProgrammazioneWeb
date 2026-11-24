<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Notifications\SecurityAlertNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

/**
 * Gestisce l'aggiornamento della password di un utente verificandone la password corrente.
 */
class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    /**
     * Valida la password attuale e memorizza la nuova password cifrata.
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => $this->passwordRules(),
        ])->validateWithBag('updatePassword');

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();

        // In rescue(): un SMTP che non risponde non deve far fallire il cambio password.
        rescue(fn () => $user->notify(new SecurityAlertNotification('Password cambiata', 'la password del tuo account '.config('app.name').' è stata cambiata dal profilo.')));
    }
}
