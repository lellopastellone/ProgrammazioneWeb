<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avviso al vecchio indirizzo quando cambia l'email di un account (dal profilo o dall'organizer):
 * un cambio non autorizzato, che dirotterebbe il recupero password, non passa inosservato.
 */
class EmailChangedNotification extends Notification
{
    /**
     * Inizializza la notifica con l'utente la cui email è appena cambiata e l'eventuale autore della modifica.
     */
    public function __construct(public User $user, public ?User $by = null) {}

    /**
     * Definisce i canali di recapito della notifica (email).
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Crea il messaggio con il nuovo indirizzo dell'account e il testo contestualizzato in base a chi ha effettuato la modifica.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $byAdmin = $this->by && $this->by->id !== $this->user->id;

        $mail = (new MailMessage)
            ->subject('Email del tuo account cambiata')
            ->greeting("Ciao {$this->user->name},");

        if ($byAdmin) {
            $mail->line("l'indirizzo email del tuo account ".config('app.name')." è stato aggiornato dall'amministratore in {$this->user->email}.")
                ->line("Se ritieni si tratti di un errore, contatta l'amministratore.");
        } else {
            $mail->line("l'indirizzo email del tuo account ".config('app.name')." è stato cambiato in {$this->user->email}.")
                ->line("Se non sei stato tu, contatta subito l'amministratore.");
        }

        return $mail->salutation(config('app.name'));
    }
}
