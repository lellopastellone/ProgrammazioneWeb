<?php

namespace Database\Seeders;

use App\Models\EmailDomain;
use App\Models\MailSetting;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Popola il database con domini email, impostazioni SMTP, utenti e team dimostrativi.
 */
class UserSeeder extends Seeder
{
    /**
     * Inserisce gli utenti demo suddivisi per ruolo e ne assegna i relativi team aziendali.
     */
    public function run(): void
    {
        // Domini email aziendali ammessi dal sistema
        collect(['example.com', 'flowdesk.it', 'example.org', 'example.net'])
            ->each(fn (string $domain) => EmailDomain::firstOrCreate(['domain' => $domain]));

        // Configurazione del server SMTP demo (visibile e modificabile dall'organizer in /settings/mail)
        MailSetting::firstOrCreate(
            ['from_address' => 'noreply@example.com'],
            [
                'host' => '127.0.0.1',
                'port' => 1025,
                'username' => 'flowdesk-demo',
                'password' => 'secret',
            ]
        );

        // 1. Organizer (amministratore aziendale)
        User::factory()->create([
            'name' => 'Olivia',
            'surname' => 'Owner',
            'email' => 'organizer@example.com',
        ])->assignRole('organizer');

        // 2. Project Manager
        User::factory()->create([
            'name' => 'Paolo',
            'surname' => 'Manager',
            'email' => 'pm@example.com',
        ])->assignRole('project_manager');

        User::factory()->create([
            'name' => 'Giulia',
            'surname' => 'Manager',
            'email' => 'pm2@example.com',
        ])->assignRole('project_manager');

        // 3. Team aziendali con descrizioni descrittive e professionali
        $backendTeam = Team::create([
            'name' => 'Backend',
            'description' => 'Architettura API RESTful, gestione database relazionali, sicurezza dei dati e logica applicativa.',
        ]);

        $frontendTeam = Team::create([
            'name' => 'Frontend',
            'description' => 'Sviluppo interfacce web responsive, componenti Blade riutilizzabili, accessibilità WCAG e user experience.',
        ]);

        $qaTeam = Team::create([
            'name' => 'QA',
            'description' => 'Quality Assurance, automazione dei test end-to-end, test di carico e verifica delle regressioni.',
        ]);

        $devOpsTeam = Team::create([
            'name' => 'DevOps',
            'description' => 'Infrastruttura cloud, container Docker, pipeline di CI/CD e monitoraggio della disponibilità dei servizi.',
        ]);

        // 4. Utente demo Member principale (ha competenze full-stack, assegnato sia a Backend che a Frontend)
        $demoMember = User::factory()->create([
            'name' => 'Marco',
            'surname' => 'Member',
            'email' => 'member@example.com',
        ]);
        $demoMember->assignRole('member');
        $demoMember->teams()->attach([$backendTeam->id, $frontendTeam->id]);

        // 5. Altri colleghi del team tecnico con nomi e cognomi italiani realistici
        $colleagues = [
            [
                'name' => 'Sofia',
                'surname' => 'Romano',
                'email' => 'sofia.romano@example.com',
                'teams' => [$frontendTeam->id],
            ],
            [
                'name' => 'Luca',
                'surname' => 'Conti',
                'email' => 'luca.conti@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Elena',
                'surname' => 'Ferrari',
                'email' => 'elena.ferrari@example.com',
                'teams' => [$qaTeam->id],
            ],
            [
                'name' => 'Matteo',
                'surname' => 'Ricci',
                'email' => 'matteo.ricci@example.com',
                'teams' => [$devOpsTeam->id],
            ],
            [
                'name' => 'Alessandro',
                'surname' => 'De Luca',
                'email' => 'alessandro.deluca@example.com',
                'teams' => [$frontendTeam->id],
            ],
            [
                'name' => 'Chiara',
                'surname' => 'Moretti',
                'email' => 'chiara.moretti@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Davide',
                'surname' => 'Esposito',
                'email' => 'davide.esposito@example.com',
                'teams' => [$qaTeam->id],
            ],
            [
                'name' => 'Francesca',
                'surname' => 'Marino',
                'email' => 'francesca.marino@example.com',
                'teams' => [$devOpsTeam->id, $backendTeam->id],
            ],
            // Utente disattivato: utile in sede d'esame per mostrare la funzionalità di riattivazione/disattivazione da parte dell'organizer
            [
                'name' => 'Simone',
                'surname' => 'Rinaldi',
                'email' => 'simone.rinaldi@example.com',
                'teams' => [$backendTeam->id],
                'active' => false,
            ],
            // Ulteriori colleghi per popolare l'organico (>25 utenti) consentendo la paginazione e il limite 20+1 dei candidati
            [
                'name' => 'Alessia',
                'surname' => 'Bianchi',
                'email' => 'alessia.bianchi@example.com',
                'teams' => [$frontendTeam->id],
            ],
            [
                'name' => 'Marco',
                'surname' => 'Verdi',
                'email' => 'marco.verdi@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Giorgia',
                'surname' => 'Rossi',
                'email' => 'giorgia.rossi@example.com',
                'teams' => [$qaTeam->id],
            ],
            [
                'name' => 'Stefano',
                'surname' => 'Galli',
                'email' => 'stefano.galli@example.com',
                'teams' => [$devOpsTeam->id],
            ],
            [
                'name' => 'Valentina',
                'surname' => 'Bruni',
                'email' => 'valentina.bruni@example.com',
                'teams' => [$frontendTeam->id],
            ],
            [
                'name' => 'Federico',
                'surname' => 'Costa',
                'email' => 'federico.costa@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Marta',
                'surname' => 'Bellini',
                'email' => 'marta.bellini@example.com',
                'teams' => [$qaTeam->id],
            ],
            [
                'name' => 'Andrea',
                'surname' => 'Fontana',
                'email' => 'andrea.fontana@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Camilla',
                'surname' => 'Serra',
                'email' => 'camilla.serra@example.com',
                'teams' => [$frontendTeam->id],
            ],
            [
                'name' => 'Claudio',
                'surname' => 'Greco',
                'email' => 'claudio.greco@example.com',
                'teams' => [$devOpsTeam->id],
            ],
            [
                'name' => 'Beatrice',
                'surname' => 'Villa',
                'email' => 'beatrice.villa@example.com',
                'teams' => [$qaTeam->id],
            ],
            [
                'name' => 'Lorenzo',
                'surname' => 'Pellegrini',
                'email' => 'lorenzo.pellegrini@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Silvia',
                'surname' => 'Monti',
                'email' => 'silvia.monti@example.com',
                'teams' => [$frontendTeam->id],
            ],
            [
                'name' => 'Gabriele',
                'surname' => 'Ferri',
                'email' => 'gabriele.ferri@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Irene',
                'surname' => 'Vitali',
                'email' => 'irene.vitali@example.com',
                'teams' => [$qaTeam->id],
            ],
            [
                'name' => 'Riccardo',
                'surname' => 'Parisi',
                'email' => 'riccardo.parisi@example.com',
                'teams' => [$devOpsTeam->id],
            ],
            [
                'name' => 'Alberto',
                'surname' => 'Gentile',
                'email' => 'alberto.gentile@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Martina',
                'surname' => 'Lombardi',
                'email' => 'martina.lombardi@example.com',
                'teams' => [$frontendTeam->id],
            ],
            [
                'name' => 'Tommaso',
                'surname' => 'Colombo',
                'email' => 'tommaso.colombo@example.com',
                'teams' => [$devOpsTeam->id],
            ],
            [
                'name' => 'Fabio',
                'surname' => 'Testa',
                'email' => 'fabio.testa@example.com',
                'teams' => [$qaTeam->id],
                'active' => false,
            ],
            // Componenti aggiuntivi per il team Backend (>25 membri) per verificare e mostrare a lezione/esame la paginazione nei team
            [
                'name' => 'Roberto',
                'surname' => 'Santoro',
                'email' => 'roberto.santoro@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Ilaria',
                'surname' => 'Ruggiero',
                'email' => 'ilaria.ruggiero@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Vincenzo',
                'surname' => 'Caruso',
                'email' => 'vincenzo.caruso@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Silvia',
                'surname' => 'D\'Amico',
                'email' => 'silvia.damico@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Beatrice',
                'surname' => 'Mariani',
                'email' => 'beatrice.mariani@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Michele',
                'surname' => 'Ferrara',
                'email' => 'michele.ferrara@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Daniele',
                'surname' => 'Marchetti',
                'email' => 'daniele.marchetti@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Camilla',
                'surname' => 'De Angelis',
                'email' => 'camilla.deangelis@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Stefano',
                'surname' => 'Vitale',
                'email' => 'stefano.vitale@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Nicola',
                'surname' => 'Palumbo',
                'email' => 'nicola.palumbo@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Giorgia',
                'surname' => 'Bassi',
                'email' => 'giorgia.bassi@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Lorenzo',
                'surname' => 'Rizzi',
                'email' => 'lorenzo.rizzi@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Christian',
                'surname' => 'Sartori',
                'email' => 'christian.sartori@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Veronica',
                'surname' => 'Leone',
                'email' => 'veronica.leone@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Emanuele',
                'surname' => 'Fabbri',
                'email' => 'emanuele.fabbri@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Serena',
                'surname' => 'Bernardi',
                'email' => 'serena.bernardi@example.com',
                'teams' => [$backendTeam->id],
            ],
            [
                'name' => 'Filippo',
                'surname' => 'Barone',
                'email' => 'filippo.barone@example.com',
                'teams' => [$backendTeam->id],
            ],
        ];

        foreach ($colleagues as $data) {
            $user = User::factory()->create([
                'name' => $data['name'],
                'surname' => $data['surname'],
                'email' => $data['email'],
                'active' => $data['active'] ?? true,
            ]);
            $user->assignRole('member');
            $user->teams()->attach($data['teams']);
        }
    }
}
