<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Rappresenta un dominio email aziendale autorizzato alla registrazione e al login.
 */
class EmailDomain extends Model
{
    protected $fillable = ['domain'];
}
