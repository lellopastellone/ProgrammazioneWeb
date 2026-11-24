<?php

/**
 * Registrazione dei Service Provider dell'applicazione (servizi applicativi di base,
 * configurazione dell'autenticazione con Fortify e personalizzazione di Jetstream).
 */

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\JetstreamServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    JetstreamServiceProvider::class,
];
