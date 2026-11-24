<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avviso all'utente quando cambia qualcosa che protegge il suo account (password cambiata, 2FA disattivata):
 * un cambio non autorizzato non passa inosservato.
 */
class SecurityAlertNotification extends Notification
{
    /**
     * Inizializza la notifica con l'oggetto della mail e la frase che descrive cosa è cambiato.
     */
    public function __construct(public string $subject, public string $line) {}

    /**
     * Definisce i canali di recapito della notifica (email).
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Crea il messaggio con la descrizione della modifica.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->greeting("Ciao {$notifiable->name},")
            ->line($this->line)
            ->line("Se non sei stato tu, contatta subito l'amministratore.")
            ->salutation(config('app.name'));
    }
}
