<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Notifications\SecurityAlertNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

/**
 * Gestisce la validazione e il reset della password dimenticata di un utente.
 */
class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Valida e aggiorna la nuova password nel record dell'utente.
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();

        // In rescue(): un SMTP che non risponde non deve far fallire il reset. Il primo accesso (/set-password) non passa da qui.
        rescue(fn () => $user->notify(new SecurityAlertNotification('Password cambiata', 'la password del tuo account '.config('app.name').' è stata reimpostata con il recupero password.')));
    }
}
