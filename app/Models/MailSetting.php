<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Gestisce la configurazione del server SMTP per l'invio delle email applicative.
 */
class MailSetting extends Model
{
    protected $fillable = ['host', 'port', 'username', 'password', 'from_address'];

    protected $hidden = ['password'];

    /**
     * Recupera le impostazioni SMTP salvate o crea un'istanza precompilata da environment.
     */
    public static function current(): self
    {
        return static::first() ?? new static([
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'username' => config('mail.mailers.smtp.username'),
            'from_address' => config('mail.from.address'),
        ]);
    }

    /**
     * Definisce i cast degli attributi, inclusa la cifratura della password SMTP.
     */
    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'password' => 'encrypted',
        ];
    }
}
