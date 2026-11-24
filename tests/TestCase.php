<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Classe base astratta per i test con configurazione iniziale di ruoli e utente organizer.
 */
abstract class TestCase extends BaseTestCase
{
    /** Ruoli e permessi Spatie seminati dopo ogni RefreshDatabase. */
    protected string $seeder = RolesAndPermissionsSeeder::class;

    /**
     * Inizializza l'ambiente di test disabilitando Vite e predisponendo un organizer predefinito.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        // App già configurata: senza un organizer EnsureSetupIsComplete rimanderebbe tutto a /setup.
        // SetupTest lo rimuove per partire da un'installazione vuota.
        User::factory()->create()->assignRole('organizer');
    }
}
