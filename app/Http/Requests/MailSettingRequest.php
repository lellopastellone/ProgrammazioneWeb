<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida i parametri di configurazione del server SMTP modificati dall'organizer.
 */
class MailSettingRequest extends FormRequest
{
    use SmtpRules;

    /**
     * Definisce le regole di validazione per i parametri di connessione SMTP.
     */
    public function rules(): array
    {
        return $this->smtpRules();
    }
}
