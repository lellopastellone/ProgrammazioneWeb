{{-- Landing page pubblica di benvenuto con presentazione della piattaforma e link di accesso --}}
<x-guest-layout title="Workspace collaborativo per team moderni">
    <div class="min-vh-100 d-flex flex-column justify-content-between">
        {{-- Barra di navigazione pubblica superiore con logo e link di accesso --}}
        <header class="border-bottom bg-white sticky-top">
            <div class="container-xl py-3 d-flex justify-content-between align-items-center">
                <a href="{{ url('/') }}" class="d-flex align-items-center gap-2 text-decoration-none text-dark">
                    <x-application-mark />
                    <span class="fs-5 fw-bold tracking-tight text-dark">Flow<span class="text-primary">Desk</span></span>
                </a>
                <div class="d-flex align-items-center gap-2">
                    @auth
                        <a class="btn btn-primary btn-sm px-3 shadow-sm" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    @else
                        <a class="btn btn-outline-secondary btn-sm px-3" href="{{ route('login') }}">Accedi</a>
                    @endauth
                </div>
            </div>
        </header>

        {{-- Sezione principale Hero: Titolo, call to action e mockup interattivo dello sprint --}}
        <main class="container-xl py-5 my-auto">
            <div class="row align-items-center gy-5 py-4">
                <div class="col-lg-7">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-primary-subtle text-primary border border-primary-subtle small fw-medium mb-3">
                        <i class="bi bi-stars"></i>
                        <span>Piattaforma di collaborazione agile & workflow</span>
                    </div>
                    <h1 class="display-4 fw-bold text-dark tracking-tight mb-3">
                        Progetti, iterazioni e team in un unico workspace.
                    </h1>
                    <p class="lead text-secondary mb-4 fs-5">
                        Coordina il ciclo di vita dei progetti software: pianifica backlog ed iterazioni sprint, gestisci workflow con board Kanban interattive e monitora il carico di lavoro del tuo team in tempo reale.
                    </p>
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        @auth
                            <a class="btn btn-primary btn-lg px-4 shadow-sm" href="{{ route('dashboard') }}">
                                Vai alla tua Dashboard <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        @else
                            <a class="btn btn-primary btn-lg px-4 shadow-sm" href="{{ route('login') }}">
                                Inizia ora <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        @endauth
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card shadow-lg border-0 rounded-4 p-2 bg-gradient" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.08) 0%, rgba(14, 165, 233, 0.08) 100%);">
                        <div class="card-body p-4 bg-white rounded-3 border">
                            <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="bi bi-kanban"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">Sprint 3 · Prenotazione</div>
                                        <div class="text-secondary" style="font-size: 0.75rem;">App prenotazioni ambulatorio</div>
                                    </div>
                                </div>
                                <x-status :value="App\Enums\ProjectStatus::InProgress" />
                            </div>

                            {{-- Lo stesso sprint dei dati demo (ProjectSeeder): un obiettivo, task in sequenza. --}}
                            <div class="d-flex flex-column gap-3">
                                @foreach ([
                                    ['API di creazione prenotazione', App\Enums\TaskStatus::Completed],
                                    ['Schermata di conferma', App\Enums\TaskStatus::InReview],
                                    ['Promemoria via SMS', App\Enums\TaskStatus::InProgress],
                                    ['Test di prenotazioni simultanee', App\Enums\TaskStatus::Todo],
                                ] as [$title, $status])
                                    <div class="p-3 rounded-3 bg-light border d-flex justify-content-between align-items-center gap-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi {{ $status->icon() }} text-{{ $status->color() }}"></i>
                                            <span class="small fw-medium text-dark">{{ $title }}</span>
                                        </div>
                                        <x-status :value="$status" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Griglia di presentazione delle funzionalità chiave della piattaforma --}}
            <div class="row g-4 pt-5 mt-2 border-top">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4 rounded-3">
                        <div class="rounded-3 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                            <i class="bi bi-kanban fs-4"></i>
                        </div>
                        <h3 class="h5 fw-bold text-dark mb-2">Backlog & Kanban Dinamico</h3>
                        <p class="text-secondary small mb-0">Visualizza le attività con colonne interattive, stima delle ore, date di scadenza e filtri avanzati per iterazione.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4 rounded-3">
                        <div class="rounded-3 bg-info-subtle text-info d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <h3 class="h5 fw-bold text-dark mb-2">Ruoli & Team Dedicati</h3>
                        <p class="text-secondary small mb-0">Dashboard su misura per Organizer, Project Manager e Sviluppatori con permessi granulari e carichi di lavoro bilanciati.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4 rounded-3">
                        <div class="rounded-3 bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                            <i class="bi bi-calendar3 fs-4"></i>
                        </div>
                        <h3 class="h5 fw-bold text-dark mb-2">Calendario & Deadline</h3>
                        <p class="text-secondary small mb-0">Tracciamento visivo delle scadenze mensili con indicatori di stato e task in ritardo sempre in evidenza.</p>
                    </div>
                </div>
            </div>
        </main>

        {{-- Footer pubblico con copyright e tecnologie utilizzate --}}
        <footer class="border-top py-4 bg-white mt-5">
            <div class="container-xl d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 small text-secondary">
                <div class="d-flex align-items-center gap-2">
                    <x-application-mark />
                    <span>&copy; {{ date('Y') }} FlowDesk. Tutti i diritti riservati.</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span>Sviluppato con Laravel, Bootstrap & Livewire</span>
                </div>
            </div>
        </footer>
    </div>
</x-guest-layout>
