<?php

namespace App\Providers;

use App\Models\MailSetting;
use App\Models\User;
use App\Notifications\SecurityAlertNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;

/**
 * Registra e inizializza i servizi applicativi, inclusi mailer e reset password.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra i servizi dell'applicazione all'interno del container.
     */
    public function register(): void
    {
        //
    }

    /**
     * Inizializza la configurazione dinamica dell'SMTP e la notifica di recupero password.
     */
    public function boot(): void
    {
        // Nelle mail le variabili non diventano markdown: un nome come "[clicca](https://...)" resta testo, non un link.
        Markdown::withSecuredEncoding();

        // Link di paginazione (lista utenti) con il markup di Bootstrap 5, non di Tailwind.
        Paginator::useBootstrapFive();

        // 2FA disattivata dal profilo (basta la password): avviso all'utente, così chi conosce la password non la spegne
        // di nascosto per aggirare two-factor.verified. Solo se era confermata: annullare l'attivazione non conta.
        Event::listen(TwoFactorAuthenticationDisabled::class, function (TwoFactorAuthenticationDisabled $event) {
            if (filled($event->user->getPrevious()['two_factor_confirmed_at'] ?? null)) {
                rescue(fn () => $event->user->notify(new SecurityAlertNotification(
                    'Verifica in due passaggi disattivata',
                    'la verifica in due passaggi del tuo account '.config('app.name').' è stata disattivata dal profilo.',
                )));
            }
        });

        // Mail di recupero password ("Password dimenticata?"): stesso template e stile della mail di benvenuto.
        // Link costruito su APP_URL e non sull'header Host della richiesta (anonima): un Host falso porterebbe il token altrove.
        ResetPassword::toMailUsing(fn (User $user, string $token) => (new MailMessage)
            ->subject('Recupero password')
            ->markdown('mail.password', [
                'user' => $user,
                'title' => 'Recupero password',
                'intro' => 'abbiamo ricevuto una richiesta di recupero della password del tuo account.',
                'action' => 'Scegli una nuova password',
                'url' => config('app.url').route('password.reset', ['token' => $token, 'email' => $user->email], absolute: false),
                'expires' => config('auth.passwords.users.expire').' minuti',
                'outro' => 'Se non sei stato tu, ignora questa mail: la password resta quella di prima.',
            ]));

        // L'SMTP scelto al setup sostituisce quello del .env. Si legge solo quando serve il mailer, non a ogni richiesta.
        $this->app->afterResolving('mail.manager', function () {
            if (! $smtp = MailSetting::first()) {
                return;
            }

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp' => [
                    ...config('mail.mailers.smtp'),
                    'url' => null,
                    'scheme' => null,  // Impostato a null per far scegliere a Symfony Mailer la cifratura corretta in base alla porta (porta 465: SSL diretto, porta 587: STARTTLS).
                    'host' => $smtp->host,
                    'port' => $smtp->port,
                    'username' => $smtp->username,
                    'password' => $smtp->password,
                    'timeout' => 10,
                ],
                'mail.from.address' => $smtp->from_address,
            ]);
        });
    }
}
