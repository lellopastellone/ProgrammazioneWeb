<?php

namespace App\Providers;

use App\Livewire\UpdateProfileInformationForm;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Jetstream;
use Livewire\Livewire;

/**
 * Configura i servizi, le autorizzazioni dei token API e i componenti di Laravel Jetstream.
 */
class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Registra i servizi di Jetstream all'interno del container.
     */
    public function register(): void
    {
        //
    }

    /**
     * Inizializza i permessi, i componenti Livewire e il prefetch delle risorse Vite.
     */
    public function boot(): void
    {
        $this->configurePermissions();

        // Sostituisce il componente predefinito di Jetstream per gestire la notifica flash e il redirect alla rimozione della foto profilo.
        Livewire::component('profile.update-profile-information-form', UpdateProfileInformationForm::class);

        Vite::prefetch(concurrency: 3);
    }

    /**
     * Configura i permessi disponibili per i token API personali.
     */
    protected function configurePermissions(): void
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        Jetstream::permissions([
            'create',
            'read',
            'update',
            'delete',
        ]);
    }
}
