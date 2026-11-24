<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifica inviata ai nuovi utenti contenente il link per impostare la prima password.
 */
class WelcomeNotification extends Notification
{
    /**
     * Inizializza la notifica memorizzando il token di attivazione.
     */
    public function __construct(public string $token) {}

    /**
     * Definisce i canali di recapito della notifica (email).
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Crea il messaggio email con il collegamento temporaneo per la scelta della password.
     */
    public function toMail(User $user): MailMessage
    {
        return (new MailMessage)
            ->subject('Benvenuto in '.config('app.name'))
            ->markdown('mail.password', [
                'user' => $user,
                'title' => 'Benvenuto!',
                'intro' => 'è stato creato il tuo account. Scegli la password con cui accederai.',
                'action' => 'Scegli la password',
                // Su APP_URL, non sull'host della richiesta (come la mail di recupero in AppServiceProvider).
                'url' => config('app.url').route('password.set', ['token' => $this->token, 'email' => $user->email], absolute: false),
                'expires' => '7 giorni',
                'outro' => 'Se scade, usa «Password dimenticata?» dalla pagina di accesso.',
            ]);
    }
}
