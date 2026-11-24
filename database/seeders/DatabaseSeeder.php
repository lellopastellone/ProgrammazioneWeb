<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeder principale dell'applicazione che orchestra l'esecuzione di ruoli, utenti e progetti.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Esegue i seeder dell'applicazione nell'ordine di dipendenza previsto.
     */
    public function run(): void
    {
        // In produzione niente utenti demo (password "password", domini example.*): solo ruoli e permessi.
        if (app()->isProduction()) {
            $this->call(RolesAndPermissionsSeeder::class);

            return;
        }

        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            ProjectSeeder::class,
        ]);
    }
}
