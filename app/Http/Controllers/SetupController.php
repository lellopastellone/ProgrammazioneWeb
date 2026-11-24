<?php

namespace App\Http\Controllers;

use App\Http\Requests\SetupRequest;
use App\Models\EmailDomain;
use App\Models\MailSetting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Gestisce la procedura di configurazione iniziale (primo organizer, domini aziendali e SMTP).
 */
class SetupController extends Controller
{
    /**
     * Mostra la schermata di configurazione iniziale dell'applicazione.
     */
    public function create(): View
    {
        return view('setup');
    }

    /**
     * Inizializza ruoli, domini, impostazioni SMTP e crea il primo account organizer autenticandolo.
     */
    public function store(SetupRequest $request): RedirectResponse
    {
        // Idempotente: crea ruoli e permessi se non è stato lanciato nessun seeder.
        (new RolesAndPermissionsSeeder)->run();

        $request->domains()->each(fn (string $domain) => EmailDomain::firstOrCreate(['domain' => $domain]));
        MailSetting::create($request->smtp());

        // Due invii simultanei potrebbero teoricamente creare due organizer; rischio trascurabile trattandosi della prima configurazione iniziale.
        $user = User::create($request->safe()->only(['name', 'surname', 'email', 'password']));
        $user->assignRole('organizer');

        // Autentica subito l'organizer appena creato nella sessione, evitando di chiedergli il login manuale.
        Auth::login($user);

        return to_route('dashboard');
    }
}
