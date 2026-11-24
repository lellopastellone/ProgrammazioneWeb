<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse as FailedPasswordResetLinkRequestResponseContract;
use Laravel\Fortify\Contracts\FailedPasswordResetResponse as FailedPasswordResetResponseContract;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\FailedPasswordResetResponse;
use Laravel\Fortify\Http\Responses\SuccessfulPasswordResetLinkRequestResponse;

/**
 * Configura i flussi di autenticazione, rate limiting e sicurezza gestiti da Laravel Fortify.
 */
class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Registra i servizi di Fortify all'interno del container.
     */
    public function register(): void
    {
        //
    }

    /**
     * Configura le azioni di autenticazione, il blocco utenti inattivi e i limiti di frequenza.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        // Login rifiutato per gli utenti disattivati. Un solo hash in ogni caso (anche senza utente, e "active" controllato
        // dopo Hash::check): il tempo di risposta non rivela quali email esistono o sono disattivate.
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->input(Fortify::username()))->first();
            $password = (string) $request->input('password');

            if (! $user) {
                Hash::make($password);

                return null;
            }

            return Hash::check($password, $user->password) && $user->active ? $user : null;
        });

        // Recupero password: stessa risposta per email esistenti e non (anche il throttle, che scatta solo su email
        // esistenti, risponde "link inviato"), e un reset con email sconosciuta dà lo stesso errore di un token sbagliato.
        $this->app->bind(FailedPasswordResetLinkRequestResponseContract::class, fn () => new SuccessfulPasswordResetLinkRequestResponse(Password::RESET_LINK_SENT));
        $this->app->bind(FailedPasswordResetResponseContract::class, fn () => new FailedPasswordResetResponse(Password::INVALID_TOKEN));

        // Richieste di recupero e di prima password: 10 al minuto per IP (enumerazione, mail a raffica). Il limiter è
        // agganciato a tutte le route di Fortify (config/fortify.php) e a /set-password, ma conta solo questi tre POST.
        RateLimiter::for('password-reset', fn (Request $request) => $request->routeIs('password.email', 'password.update', 'password.set.store')
            ? Limit::perMinute(10)->by($request->ip())
            : Limit::none());

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
