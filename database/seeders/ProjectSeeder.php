<?php

namespace Database\Seeders;

use App\Enums\Priority;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Comment;
use App\Models\Iteration;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Popola il database con progetti demo completi di sprint, task, assegnazioni e discussioni.
 */
class ProjectSeeder extends Seeder
{
    private const PROJECTS_DATA = [
        [
            'name' => 'Portale clienti',
            'description' => 'Area riservata aziendale per la consultazione autonoma di ordini, fatture e contratti, con modulo integrato per l\'apertura di ticket di supporto tecnico.',
            'status' => ProjectStatus::InProgress,
            'pm_email' => 'pm@example.com',
            'current_sprint_index' => 1,
            'sprints' => [
                [
                    'name' => 'Sprint 1 · Fondamenta',
                    'tasks' => [
                        [
                            'title' => 'Repository Git e pipeline CI/CD',
                            'team' => 'DevOps',
                            'priority' => Priority::High,
                            'description' => 'Configurazione del repository su GitLab con branch protetti, linting automatico e pipeline di test su ogni merge request.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'matteo.ricci@example.com',
                            'comments' => [
                                ['user' => 'matteo.ricci@example.com', 'text' => 'Pipeline CI/CD configurata e collaudata con esito positivo.'],
                                ['user' => 'pm@example.com', 'text' => 'Verificata la protezione dei branch su main e staging.'],
                            ],
                        ],
                        [
                            'title' => 'Schema database relazionale clienti e ordini',
                            'team' => 'Backend',
                            'priority' => Priority::High,
                            'description' => 'Modellazione entità clienti, contratti, ordini e fatture con migrazioni Laravel e chiavi esterne con vincoli appropriati.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'member@example.com',
                            'comments' => [
                                ['user' => 'member@example.com', 'text' => 'Migration eseguite e documentato il diagramma ER.'],
                            ],
                        ],
                        [
                            'title' => 'Layout base responsive dell\'area riservata',
                            'team' => 'Frontend',
                            'priority' => Priority::Medium,
                            'description' => 'Header, sidebar di navigazione, breadcrumb e scheletro della dashboard responsive fino a risoluzioni mobile.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'sofia.romano@example.com',
                            'comments' => [
                                ['user' => 'sofia.romano@example.com', 'text' => 'Layout testato su browser moderni (Chrome, Firefox, Safari) e mobile.'],
                            ],
                        ],
                        [
                            'title' => 'Ambiente di collaudo e staging su Docker',
                            'team' => 'DevOps',
                            'priority' => Priority::Medium,
                            'description' => 'Server di staging containerizzato aggiornato automaticamente con database isolato e seed di prova.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'francesca.marino@example.com',
                            'comments' => [
                                ['user' => 'francesca.marino@example.com', 'text' => 'Container Docker avviati e certificati SSL configurati.'],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Sprint 2 · Autenticazione e profilo',
                    'tasks' => [
                        [
                            'title' => 'API di autenticazione e gestione sessioni',
                            'team' => 'Backend',
                            'priority' => Priority::Critical,
                            'description' => 'Endpoint di login e logout con protezione CSRF, rate limiting a 5 tentativi/minuto e log degli accessi.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'member@example.com',
                            'offset_start' => -7,
                            'offset_end' => -3,
                            'comments' => [
                                ['user' => 'member@example.com', 'text' => 'Implementati endpoint con rate limiting e invalidazione sessione al logout.'],
                                ['user' => 'elena.ferrari@example.com', 'text' => 'Test di penetrazione e verifica brute-force superati.'],
                                ['user' => 'pm@example.com', 'text' => 'Validato e mergiato su main per il rilascio in staging.'],
                            ],
                        ],
                        [
                            'title' => 'Interfaccia grafica di login e reset password',
                            'team' => 'Frontend',
                            'priority' => Priority::High,
                            'description' => 'Form di accesso responsive collegato alle API, con feedback visivo degli errori e link al recupero password.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'sofia.romano@example.com',
                            'offset_start' => -7,
                            'offset_end' => -2,
                            'comments' => [
                                ['user' => 'sofia.romano@example.com', 'text' => 'Form completato con validazione asincrona e accessibilità WCAG.'],
                                ['user' => 'pm@example.com', 'text' => 'Interfaccia approvata, rispetta le linee guida del design system.'],
                            ],
                        ],
                        [
                            'title' => 'Recupero credenziali via email con token',
                            'team' => 'Backend',
                            'priority' => Priority::High,
                            'description' => 'Invio asincrono del link di reset con token crittografato monouso a scadenza di 60 minuti e pagina di reimpostazione.',
                            'status' => TaskStatus::InProgress,
                            'assignee_email' => 'member@example.com',
                            'offset_start' => -4,
                            'offset_end' => 3,
                            'comments' => [
                                ['user' => 'member@example.com', 'text' => 'Configurato il broker delle password con token monouso. Sto rifinendo il template HTML.'],
                                ['user' => 'pm@example.com', 'text' => 'Ricordati di verificare che il link usi l\'host configurato nel mail setting.'],
                            ],
                        ],
                        [
                            'title' => 'Pannello profilo utente e preferenze',
                            'team' => 'Frontend',
                            'priority' => Priority::Medium,
                            'description' => 'Pagina dedicata in cui il cliente visualizza e aggiorna recapiti aziendali, indirizzi di spedizione e password.',
                            'status' => TaskStatus::InReview,
                            'assignee_emails' => ['sofia.romano@example.com', 'member@example.com'],
                            'offset_start' => -4,
                            'offset_end' => 0,
                            'comments' => [
                                ['user' => 'sofia.romano@example.com', 'text' => 'Completata la grafica del profilo; lascio a Marco il collegamento con il controller.'],
                                ['user' => 'member@example.com', 'text' => 'Controller collegato e testato. Aperta merge request !14 e passo il task in review al PM.'],
                                ['user' => 'pm@example.com', 'text' => 'La revisiono questo pomeriggio insieme alla verifica dei permessi.'],
                            ],
                        ],
                        [
                            'title' => 'Suite di test end-to-end su autenticazione',
                            'team' => 'QA',
                            'priority' => Priority::High,
                            'description' => 'Scenari automatici con Pest e Dusk su login, logout, lockout dopo tentativi falliti e reset password.',
                            'status' => TaskStatus::InProgress,
                            'assignee_emails' => ['elena.ferrari@example.com', 'member@example.com'],
                            'offset_start' => -6,
                            'offset_end' => -2, // IN RITARDO (overdue)
                            'comments' => [
                                ['user' => 'pm@example.com', 'text' => 'È oltre la scadenza: c\'è qualcosa che vi blocca? Abbiamo la demo a breve.'],
                                ['user' => 'elena.ferrari@example.com', 'text' => 'Abbiamo dovuto mockare il server SMTP per il recapito del token. Chiudiamo i test oggi.'],
                                ['user' => 'member@example.com', 'text' => 'Sto supportando Elena con il mock del MailSetting.'],
                            ],
                        ],
                        [
                            'title' => 'Monitoraggio accessi falliti e alert brute-force',
                            'team' => 'DevOps',
                            'priority' => Priority::Low,
                            'description' => 'Configurazione allarmi Prometheus per invio alert quando i login falliti superano la soglia di sicurezza.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'matteo.ricci@example.com',
                            'offset_start' => 1,
                            'offset_end' => 6,
                        ],
                    ],
                ],
                [
                    'name' => 'Sprint 3 · Ordini e fatture',
                    'tasks' => [
                        [
                            'title' => 'API elenco ordini con paginazione e filtri',
                            'team' => 'Backend',
                            'priority' => Priority::High,
                            'description' => 'Endpoint paginato per consultare gli ordini del cliente con filtri per data, importo e stato di spedizione.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'luca.conti@example.com',
                        ],
                        [
                            'title' => 'Tabella storico ordini e dettaglio articoli',
                            'team' => 'Frontend',
                            'priority' => Priority::High,
                            'description' => 'Vista tabellare con ricerca istantanea, badge di stato ordine e visualizzazione righe articolo acquistate.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'sofia.romano@example.com',
                        ],
                        [
                            'title' => 'Generazione e download fatture in formato PDF',
                            'team' => 'Backend',
                            'priority' => Priority::Medium,
                            'description' => 'Generazione server-side del documento PDF della fattura a partire dai dati contabili dell\'ordine.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'member@example.com',
                        ],
                        [
                            'title' => 'Test di regressione isolamento tenant clienti',
                            'team' => 'QA',
                            'priority' => Priority::Medium,
                            'description' => 'Verifica di sicurezza che ciascun cliente autenticato possa visualizzare unicamente i propri ordini e fatture.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'davide.esposito@example.com',
                        ],
                    ],
                ],
                [
                    'name' => 'Sprint 4 · Assistenza e ticket di supporto',
                    'tasks' => [
                        [
                            'title' => 'Modulo apertura ticket con upload allegati',
                            'team' => 'Backend',
                            'priority' => Priority::High,
                            'description' => 'Endpoint e logica per l\'invio di ticket tecnici con validazione multipart di screenshot e file di log.',
                            'status' => TaskStatus::InProgress,
                            'assignee_email' => 'member@example.com',
                            'offset_start' => 21,
                            'offset_end' => 25,
                            'comments' => [
                                ['user' => 'member@example.com', 'text' => 'Strutturata la tabella per gli allegati con storage cifrato e scansione MIME.'],
                                ['user' => 'pm@example.com', 'text' => 'Definire limite a 10MB per singolo allegato.'],
                            ],
                        ],
                        [
                            'title' => 'Interfaccia messaggistica e thread conversazioni',
                            'team' => 'Frontend',
                            'priority' => Priority::Medium,
                            'description' => 'Vista a thread per consultare lo storico dei messaggi tra cliente e operatore di supporto tecnico.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'sofia.romano@example.com',
                            'offset_start' => 23,
                            'offset_end' => 27,
                        ],
                        [
                            'title' => 'Dashboard SLA e monitoraggio tempi medi di risposta',
                            'team' => 'Backend',
                            'priority' => Priority::Medium,
                            'description' => 'Calcolo delle metriche di risoluzione e rispetto degli accordi sui livelli di servizio per la direzione.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'member@example.com',
                            'offset_start' => 25,
                            'offset_end' => 29,
                        ],
                        [
                            'title' => 'Test di carico e sicurezza sugli upload multipart',
                            'team' => 'QA',
                            'priority' => Priority::High,
                            'description' => 'Scansione antivirus e test di saturazione con upload contemporanei di file di grandi dimensioni.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'elena.ferrari@example.com',
                            'offset_start' => 28,
                            'offset_end' => 32,
                        ],
                    ],
                ],
                [
                    'name' => 'Sprint 5 · Reportistica annuale e chiusura release',
                    'tasks' => [
                        [
                            'title' => 'Integrazione sistema di feedback e rating del servizio',
                            'team' => 'Frontend',
                            'priority' => Priority::Low,
                            'description' => 'Modulo di gradimento a 5 stelle con recensione testuale al momento della chiusura del ticket di supporto.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'member@example.com',
                            'offset_start' => 35,
                            'offset_end' => 39,
                        ],
                        [
                            'title' => 'Export reportistica contabile per chiusura anno fiscale',
                            'team' => 'Backend',
                            'priority' => Priority::High,
                            'description' => 'Generazione massiva di file CSV ed Excel con il riepilogo annuale degli ordini e delle ritenute d\'acconto.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'member@example.com',
                            'offset_start' => 37,
                            'offset_end' => 42,
                        ],
                        [
                            'title' => 'Collaudo finale e certificazione di conformità GDPR',
                            'team' => 'QA',
                            'priority' => Priority::Critical,
                            'description' => 'Audit completo su anonimizzazione dei dati di log e diritto all\'oblio prima del rilascio di fine anno.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'davide.esposito@example.com',
                            'offset_start' => 40,
                            'offset_end' => 45,
                        ],
                    ],
                ],
            ],
            'backlog' => [
                [
                    'title' => 'Notifiche push e email sullo stato dell\'ordine',
                    'team' => 'Backend',
                    'priority' => Priority::Low,
                    'description' => 'Email automatica e notifica in app quando l\'ordine passa a Spedito o Consegnato dal corriere.',
                    'assignee_email' => 'member@example.com',
                ],
                [
                    'title' => 'Supporto tema scuro automatico',
                    'team' => 'Frontend',
                    'priority' => Priority::Low,
                    'description' => 'Variante scura dell\'interfaccia selezionabile manualmente o sincronizzata con le preferenze di sistema.',
                    'assignee_email' => 'alessandro.deluca@example.com',
                ],
                [
                    'title' => 'Esportazione ordini in formato Excel/CSV',
                    'team' => 'Backend',
                    'priority' => Priority::Low,
                    'description' => 'Funzione per consentire all\'ufficio acquisti del cliente di scaricare il report ordini annuale.',
                    'assignee_email' => 'chiara.moretti@example.com',
                ],
            ],
        ],
        [
            'name' => 'App prenotazioni ambulatorio',
            'description' => 'Piattaforma sanitaria digitale per consentire ai pazienti la prenotazione delle visite specialistiche, ricezione notifiche SMS e gestione disdette in tempo reale.',
            'status' => ProjectStatus::InProgress,
            'pm_email' => 'pm2@example.com',
            'current_sprint_index' => 2,
            'sprints' => [
                [
                    'name' => 'Sprint 1 · Prototipazione e modello dati',
                    'tasks' => [
                        [
                            'title' => 'Wireframe ad alta fedeltà flussi di prenotazione',
                            'team' => 'Frontend',
                            'priority' => Priority::Medium,
                            'description' => 'Bozze grafiche validate con la segreteria medica per la scelta dello specialista, sede e slot orario.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'alessandro.deluca@example.com',
                        ],
                        [
                            'title' => 'Modello dati medici, ambulatori e disponibilità',
                            'team' => 'Backend',
                            'priority' => Priority::High,
                            'description' => 'Struttura tabelle per orari di ricevimento, ferie, ambulatori attrezzati e durate standard per prestazione.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'luca.conti@example.com',
                        ],
                        [
                            'title' => 'Impostazione repository con Docker e pipeline CI',
                            'team' => 'DevOps',
                            'priority' => Priority::Medium,
                            'description' => 'Configurazione container di sviluppo e pipeline per la validazione automatica del codice.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'francesca.marino@example.com',
                        ],
                    ],
                ],
                [
                    'name' => 'Sprint 2 · Gestione agende e calendario',
                    'tasks' => [
                        [
                            'title' => 'Algoritmo calcolo slot orari disponibili per medico',
                            'team' => 'Backend',
                            'priority' => Priority::High,
                            'description' => 'Calcolo degli intervalli liberi sottraendo le visite già fissate alle finestre di disponibilità del medico.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'chiara.moretti@example.com',
                        ],
                        [
                            'title' => 'Componente visuale calendario per selezione slot',
                            'team' => 'Frontend',
                            'priority' => Priority::High,
                            'description' => 'Visualizzazione settimanale con fasce orarie mattutine e pomeridiane prenotabili con un click.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'alessandro.deluca@example.com',
                        ],
                        [
                            'title' => 'Collaudo agende con dati reali di due ambulatori',
                            'team' => 'QA',
                            'priority' => Priority::Medium,
                            'description' => 'Verifica funzionale su agende reali con sovrapposizioni e cambi turno straordinari.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'davide.esposito@example.com',
                        ],
                    ],
                ],
                [
                    'name' => 'Sprint 3 · Prenotazione e concorrenza',
                    'tasks' => [
                        [
                            'title' => 'API di creazione e conferma prenotazione',
                            'team' => 'Backend',
                            'priority' => Priority::Critical,
                            'description' => 'Registrazione della prenotazione con occupazione dello slot e rilascio del codice identificativo univoco.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'luca.conti@example.com',
                            'offset_start' => -7,
                            'offset_end' => -3,
                            'comments' => [
                                ['user' => 'luca.conti@example.com', 'text' => 'Aggiunta transazione SQL con isolamento serializable per prevenire overbooking.'],
                                ['user' => 'pm2@example.com', 'text' => 'Verificato il flusso con simulazione di 50 richieste concorrenti.'],
                            ],
                        ],
                        [
                            'title' => 'Gestione della concorrenza e blocco temporaneo slot',
                            'team' => 'Backend',
                            'priority' => Priority::High,
                            'description' => 'Lock temporaneo di 5 minuti durante la compilazione dei dati paziente per evitare doppie prenotazioni.',
                            'status' => TaskStatus::InProgress,
                            'assignee_email' => 'luca.conti@example.com',
                            'offset_start' => -6,
                            'offset_end' => -1, // IN RITARDO (overdue)
                            'comments' => [
                                ['user' => 'pm2@example.com', 'text' => 'È oltre la scadenza: riesci a fare il push del fix per i lock temporanei?'],
                                ['user' => 'luca.conti@example.com', 'text' => 'Sto rifinendo la gestione del rollback automatico in caso di timeout della sessione.'],
                            ],
                        ],
                        [
                            'title' => 'Schermata riepilogo e conferma appuntamento',
                            'team' => 'Frontend',
                            'priority' => Priority::High,
                            'description' => 'Pagina finale con medico, data, sede, note di preparazione alla visita e pulsante di download promemoria.',
                            'status' => TaskStatus::InReview,
                            'assignee_email' => 'alessandro.deluca@example.com',
                            'offset_start' => -4,
                            'offset_end' => 0,
                            'comments' => [
                                ['user' => 'alessandro.deluca@example.com', 'text' => 'Aggiunta mappa dell\'ambulatorio e pulsante iCal per Google Calendar e Outlook.'],
                                ['user' => 'pm2@example.com', 'text' => 'Revisione completata con esito positivo: layout chiaro e leggibile.'],
                            ],
                        ],
                        [
                            'title' => 'Servizio invio promemoria automatici via SMS',
                            'team' => 'Backend',
                            'priority' => Priority::Medium,
                            'description' => 'Invio SMS 24 ore prima della visita con orario e link rapido per confermare o disdire.',
                            'status' => TaskStatus::InProgress,
                            'assignee_email' => 'member@example.com',
                            'offset_start' => -3,
                            'offset_end' => 3,
                            'comments' => [
                                ['user' => 'member@example.com', 'text' => 'Configurato gateway SMS con code asincrone Redis e template parametrizzato.'],
                            ],
                        ],
                        [
                            'title' => 'Test di stress per prenotazioni simultanee',
                            'team' => 'QA',
                            'priority' => Priority::High,
                            'description' => 'Simulazione di 100 richieste contemporanee sullo stesso slot per certificare l\'assenza di conflitti.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'davide.esposito@example.com',
                            'offset_start' => 2,
                            'offset_end' => 5,
                        ],
                    ],
                ],
                [
                    'name' => 'Sprint 4 · Pagamenti e referti digitali',
                    'tasks' => [
                        [
                            'title' => 'Procedura di disdetta appuntamento entro 24 ore',
                            'team' => 'Backend',
                            'priority' => Priority::Medium,
                            'description' => 'Annullamento della prenotazione con rimessa a disposizione dello slot e notifica al medico.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'luca.conti@example.com',
                        ],
                        [
                            'title' => 'Storico visite effettuate e referti digitali',
                            'team' => 'Frontend',
                            'priority' => Priority::Medium,
                            'description' => 'Area privata per il download sicuro dei referti medici con autenticazione rinforzata.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'alessandro.deluca@example.com',
                        ],
                        [
                            'title' => 'Integrazione modulo di pagamento ticket online',
                            'team' => 'Backend',
                            'priority' => Priority::High,
                            'description' => 'Pagamento anticipato con carta di credito e rilascio immediato della quietanza fiscale.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'chiara.moretti@example.com',
                        ],
                        [
                            'title' => 'Preparazione e rilascio pacchetti applicativi',
                            'team' => 'DevOps',
                            'priority' => Priority::High,
                            'description' => 'Generazione build firmate per ambienti di produzione e documentazione operativa.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'francesca.marino@example.com',
                        ],
                    ],
                ],
            ],
            'backlog' => [
                [
                    'title' => 'Integrazione con sistema di identificazione SPID / CIE',
                    'team' => 'Backend',
                    'priority' => Priority::Medium,
                    'description' => 'Autenticazione federata con identità digitale per la popolazione sanitaria.',
                    'assignee_email' => 'member@example.com',
                ],
                [
                    'title' => 'Geolocalizzazione ambulatori e calcolo percorso consigliato',
                    'team' => 'Frontend',
                    'priority' => Priority::Low,
                    'description' => 'Integrazione OpenStreetMap con indicazioni sui mezzi di trasporto pubblico disponibili.',
                    'assignee_email' => 'alessandro.deluca@example.com',
                ],
            ],
        ],
        [
            'name' => 'Migrazione infrastruttura Cloud AWS',
            'description' => 'Transizione graduale dell\'infrastruttura aziendale dai server fisici on-premise al cloud AWS, garantendo alta disponibilità e zero downtime.',
            'status' => ProjectStatus::Planned,
            'pm_email' => 'pm@example.com',
            'current_sprint_index' => -1, // Non ancora iniziato
            'sprints' => [
                [
                    'name' => 'Sprint 1 · Analisi e dimensionamento',
                    'tasks' => [
                        [
                            'title' => 'Mappatura architettura attuale e carichi di lavoro',
                            'team' => 'DevOps',
                            'priority' => Priority::High,
                            'description' => 'Analisi di servizi attivi, volumi di traffico nei picchi, dipendenze hardware e consumo di banda.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'matteo.ricci@example.com',
                        ],
                        [
                            'title' => 'Analisi comparativa dei costi on-premise vs AWS',
                            'team' => 'DevOps',
                            'priority' => Priority::Medium,
                            'description' => 'Stima dei costi mensili per istanze riservate, bilanciatori di carico, storage S3 e database gestito RDS.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'francesca.marino@example.com',
                        ],
                        [
                            'title' => 'Piano di migrazione database con replica continua',
                            'team' => 'Backend',
                            'priority' => Priority::High,
                            'description' => 'Strategia di sincronizzazione dei dati con replica master-replica per ridurre il fermo macchina sotto i 5 minuti.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'chiara.moretti@example.com',
                        ],
                    ],
                ],
                [
                    'name' => 'Sprint 2 · Containerizzazione e ambienti di collaudo',
                    'tasks' => [
                        [
                            'title' => 'Creazione Dockerfile ottimizzati per i servizi backend',
                            'team' => 'DevOps',
                            'priority' => Priority::High,
                            'description' => 'Container multi-stage con immagini Alpine leggere e gestione configurazioni tramite Secret Manager.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'matteo.ricci@example.com',
                        ],
                        [
                            'title' => 'Configurazione cluster AWS EKS e bilanciatori ALB',
                            'team' => 'DevOps',
                            'priority' => Priority::Critical,
                            'description' => 'Infrastruttura as Code tramite Terraform con auto-scaling orizzontale dei pod in base al carico CPU.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'francesca.marino@example.com',
                        ],
                        [
                            'title' => 'Test di carico comparativi su ambiente cloud',
                            'team' => 'QA',
                            'priority' => Priority::High,
                            'description' => 'Verifica delle prestazioni simulando il doppio del carico massimo attuale registrato in sede.',
                            'status' => TaskStatus::Todo,
                            'assignee_email' => 'elena.ferrari@example.com',
                        ],
                    ],
                ],
            ],
            'backlog' => [
                [
                    'title' => 'Configurazione disaster recovery multi-regione',
                    'team' => 'DevOps',
                    'priority' => Priority::Medium,
                    'description' => 'Replica automatica dei backup su una seconda regione AWS per garantire business continuity.',
                    'assignee_email' => 'matteo.ricci@example.com',
                ],
            ],
        ],
        [
            'name' => 'Sito istituzionale e portale news',
            'description' => 'Nuovo portale web aziendale responsive con catalogo servizi, sezione news, modulo contatti avanzato e conformità WCAG 2.1 AA.',
            'status' => ProjectStatus::Finished,
            'pm_email' => 'pm@example.com',
            'current_sprint_index' => 3, // Completato
            'sprints' => [
                [
                    'name' => 'Sprint 1 · Design e CMS',
                    'tasks' => [
                        [
                            'title' => 'Progettazione alberatura di navigazione e layout responsive',
                            'team' => 'Frontend',
                            'priority' => Priority::Medium,
                            'description' => 'Menu principale, schede servizi e template pagine istituzionali validati con la direzione.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'sofia.romano@example.com',
                        ],
                        [
                            'title' => 'Modulo CMS per pubblicazione notizie e comunicati',
                            'team' => 'Backend',
                            'priority' => Priority::Medium,
                            'description' => 'Pannello redazionale con editor visuale, gestione immagini in evidenza e scheduling pubblicazioni.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'member@example.com',
                        ],
                        [
                            'title' => 'Form contatti commerciale con filtro anti-spam',
                            'team' => 'Backend',
                            'priority' => Priority::Medium,
                            'description' => 'Smistamento automatico delle richieste all\'ufficio competente con notifica email.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'luca.conti@example.com',
                        ],
                    ],
                ],
                [
                    'name' => 'Sprint 2 · Ottimizzazione, sicurezza e rilascio',
                    'tasks' => [
                        [
                            'title' => 'Ottimizzazione SEO on-page, metadati e sitemap XML',
                            'team' => 'Frontend',
                            'priority' => Priority::Low,
                            'description' => 'Titoli descrittivi, schema markup JSON-LD e generazione sitemap per motori di ricerca.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'sofia.romano@example.com',
                        ],
                        [
                            'title' => 'Audit completo accessibilità WCAG 2.1 AA',
                            'team' => 'QA',
                            'priority' => Priority::High,
                            'description' => 'Verifica di contrasto cromatico, navigazione esclusiva da tastiera e compatibilità con screen reader.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'davide.esposito@example.com',
                        ],
                        [
                            'title' => 'Messa online e configurazione redirect 301',
                            'team' => 'DevOps',
                            'priority' => Priority::High,
                            'description' => 'Switch DNS sul dominio ufficiale con certificati SSL Let\'s Encrypt e redirect dai vecchi URL.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'matteo.ricci@example.com',
                        ],
                    ],
                ],
            ],
            'backlog' => [],
        ],
        [
            'name' => 'Gestionale magazzino e logistica v1',
            'description' => 'Prima versione del sistema di gestione magazzino per tracciamento articoli, codici a barre e movimenti di carico/scarico. Archiviato a seguito della migrazione alla nuova piattaforma.',
            'status' => ProjectStatus::Archived,
            'pm_email' => 'pm@example.com',
            'current_sprint_index' => 5, // Archiviato
            'sprints' => [
                [
                    'name' => 'Sprint 1 · Modulo anagrafiche',
                    'tasks' => [
                        [
                            'title' => 'Anagrafica articoli con codici a barre e scorte minime',
                            'team' => 'Backend',
                            'priority' => Priority::High,
                            'description' => 'Tabella articoli con unità di misura, fornitore primario e ubicazione fisica in corsia.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'chiara.moretti@example.com',
                        ],
                        [
                            'title' => 'Maschere di inserimento rapido e ricerca prodotti',
                            'team' => 'Frontend',
                            'priority' => Priority::Medium,
                            'description' => 'Interfaccia desktop ottimizzata per l\'utilizzo con lettori di barcode ottici.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'alessandro.deluca@example.com',
                        ],
                    ],
                ],
                [
                    'name' => 'Sprint 2 · Movimentazione e reportistica',
                    'tasks' => [
                        [
                            'title' => 'Registrazione bolle di carico, scarico e trasferimento',
                            'team' => 'Backend',
                            'priority' => Priority::High,
                            'description' => 'Aggiornamento automatico delle giacenze con log storico immutabile per la tracciabilità.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'luca.conti@example.com',
                        ],
                        [
                            'title' => 'Esportazione prospetto inventario per la contabilità',
                            'team' => 'Backend',
                            'priority' => Priority::Medium,
                            'description' => 'Calcolo della valorizzazione delle scorte secondo il metodo FIFO e report PDF per il bilancio.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'member@example.com',
                        ],
                        [
                            'title' => 'Collaudo operativo sul campo con gli operatori',
                            'team' => 'QA',
                            'priority' => Priority::High,
                            'description' => 'Sessione di test di due settimane affiancando gli operatori di magazzino durante l\'inventario.',
                            'status' => TaskStatus::Completed,
                            'assignee_email' => 'elena.ferrari@example.com',
                        ],
                    ],
                ],
            ],
            'backlog' => [],
        ],
        // Progetto guscio vuoto (senza PM): utile all'esame per dimostrare l'eliminazione dei progetti da parte dell'organizer
        [
            'name' => 'Piattaforma E-learning aziendale',
            'description' => 'Progetto strategico per la formazione continua del personale tecnico. In attesa di nomina del Project Manager responsabile da parte della direzione.',
            'status' => ProjectStatus::Planned,
            'pm_email' => null,
            'current_sprint_index' => -1,
            'sprints' => [],
            'backlog' => [],
        ],
    ];

    /**
     * Esegue il popolamento dei progetti demo, iterazioni, task e commenti.
     */
    public function run(): void
    {
        $organizer = User::role('organizer')->first();
        $pmsByEmail = User::role('project_manager')->get()->keyBy('email');
        $demoMember = User::firstWhere('email', 'member@example.com');
        $activeMembers = User::role('member')->active()->get();
        $teamsByName = Team::with('users')->get()->keyBy('name');

        $today = today();

        foreach (self::PROJECTS_DATA as $projectData) {
            $pm = ! empty($projectData['pm_email']) ? $pmsByEmail->get($projectData['pm_email']) : null;

            $project = Project::factory()->create([
                'name' => $projectData['name'],
                'description' => $projectData['description'],
                'status' => $projectData['status'],
                'pm_id' => $pm?->id,
                'created_by' => $organizer->id,
            ]);

            // Se il progetto non ha un PM, è un guscio vuoto (senza membri né sprint)
            if (! $pm) {
                continue;
            }

            // Assegniamo al gruppo di progetto tutti i membri attivi necessari, includendo sempre l'utente demo
            $projectMembers = $activeMembers->take(6)->push($demoMember)->unique('id');
            $project->members()->attach($projectMembers);

            $currentSprintIndex = $projectData['current_sprint_index'];
            $sprints = $projectData['sprints'];

            foreach ($sprints as $k => $sprintData) {
                $offset = $k - $currentSprintIndex;
                $sprintStarts = $today->copy()->addDays($offset * 14 - 7);
                $sprintEnds = $sprintStarts->copy()->addDays(13);

                $iteration = Iteration::factory()->for($project)->create([
                    'name' => $sprintData['name'],
                    'starts_on' => $sprintStarts,
                    'ends_on' => $sprintEnds,
                ]);

                foreach ($sprintData['tasks'] as $tIndex => $tData) {
                    $this->createTask(
                        $project,
                        $iteration,
                        $tData,
                        $sprintStarts,
                        $offset,
                        $tIndex,
                        count($sprintData['tasks']),
                        $today,
                        $activeMembers,
                        $teamsByName,
                        $demoMember
                    );
                }
            }

            // Task nel backlog (senza iterazione)
            foreach ($projectData['backlog'] as $bIndex => $bData) {
                $this->createTask(
                    $project,
                    null,
                    $bData,
                    $today->copy()->addDays(14),
                    1,
                    $bIndex,
                    max(1, count($projectData['backlog'])),
                    $today,
                    $activeMembers,
                    $teamsByName,
                    $demoMember
                );
            }
        }
    }

    /**
     * Crea un singolo task con relative date, stato, assegnatari e commenti demo.
     */
    private function createTask(
        Project $project,
        ?Iteration $iteration,
        array $data,
        Carbon $referenceDate,
        int $offset,
        int $index,
        int $total,
        Carbon $today,
        $activeMembers,
        $teamsByName,
        $demoMember
    ): void {
        // Date di inizio e fine
        if (isset($data['offset_start']) && isset($data['offset_end'])) {
            $startDate = $today->copy()->addDays($data['offset_start']);
            $endDate = $today->copy()->addDays($data['offset_end']);
        } else {
            $startDate = $referenceDate->copy()->addDays(intdiv($index * 10, max(1, $total)));
            $endDate = $startDate->copy()->addDays(3);
        }

        // Stato del task
        $status = $data['status'] ?? match (true) {
            $endDate->lt($today) => TaskStatus::Completed,
            $endDate->eq($today) => TaskStatus::InReview,
            $startDate->gt($today) => TaskStatus::Todo,
            default => TaskStatus::InProgress,
        };

        // Task completato ha completed_at
        $completedAt = $status === TaskStatus::Completed ? ($endDate->lt($today) ? $endDate : $today) : null;

        $task = Task::factory()->for($project)->create([
            'iteration_id' => $iteration?->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? 'Descrizione e note operative per lo svolgimento del task.',
            'priority' => $data['priority'] ?? Priority::Medium,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'completed_at' => $completedAt,
        ]);

        // Assegnatari specifici o dal team corrispondente
        $assigneeEmails = (array) ($data['assignee_emails'] ?? ($data['assignee_email'] ?? []));
        $assignees = collect();
        foreach ($assigneeEmails as $email) {
            if ($u = $activeMembers->firstWhere('email', $email)) {
                $assignees->push($u);
            }
        }

        if ($assignees->isEmpty()) {
            $teamName = $data['team'] ?? 'Backend';
            $teamUsers = $teamsByName->get($teamName)?->users?->where('active', true) ?? collect([$demoMember]);
            $assignees->push($teamUsers->isNotEmpty() ? $teamUsers->random() : $demoMember);
        }

        foreach ($assignees as $assignee) {
            $task->assignees()->syncWithoutDetaching([$assignee->id => ['assigned_at' => $startDate]]);
            $project->members()->syncWithoutDetaching([$assignee->id]);
        }
        $primaryAssignee = $assignees->first();

        // Creazione commenti realistici e contestuali
        if (! empty($data['comments'])) {
            foreach ($data['comments'] as $cIndex => $cData) {
                $author = User::firstWhere('email', $cData['user']) ?? $primaryAssignee;
                $commentTime = $startDate->copy()->addHours(12 + $cIndex * 14);
                if ($commentTime->gt($today)) {
                    $commentTime = $today->copy()->subHours(2 + $cIndex);
                }

                Comment::create([
                    'task_id' => $task->id,
                    'created_by' => $author->id,
                    'text' => $cData['text'],
                    'created_at' => $commentTime,
                    'updated_at' => $commentTime,
                ]);
            }
        } elseif ($offset === 0 && $status !== TaskStatus::Todo) {
            // Commento automatico coerente se nello sprint in corso
            $commentText = match ($status) {
                TaskStatus::Completed => 'Lavoro ultimato, superati tutti i test di integrazione in ambiente di staging.',
                TaskStatus::InReview => 'Merge request aperta per revisione del codice e test di sicurezza.',
                TaskStatus::InProgress => 'In corso di lavorazione: sto implementando i test di unità a supporto.',
                default => null,
            };

            if ($commentText) {
                Comment::create([
                    'task_id' => $task->id,
                    'created_by' => $primaryAssignee->id,
                    'text' => $commentText,
                    'created_at' => $startDate->copy()->addDay(),
                    'updated_at' => $startDate->copy()->addDay(),
                ]);
            }
        }

        // Alert per task scaduto (overdue)
        if ($task->isOverdue() && empty($data['comments'])) {
            Comment::create([
                'task_id' => $task->id,
                'created_by' => $project->pm_id,
                'text' => 'È oltre la scadenza prevista: ci sono blocchi tecnici o dipendenze non risolte?',
                'created_at' => $endDate->copy()->addHour(),
                'updated_at' => $endDate->copy()->addHour(),
            ]);
        }
    }
}
