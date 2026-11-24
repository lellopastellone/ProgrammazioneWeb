{{-- Barra di navigazione principale superiore con link di sezione e menu utente --}}
@php
    $user = Auth::user();
    $links = [
        ['Dashboard', route('dashboard'), request()->routeIs('dashboard'), 'bi-speedometer2'],
        ['Progetti',  route('projects.index'), request()->routeIs('projects.*', 'tasks.*', 'comments.*'), 'bi-kanban'],
    ];
    if ($user->hasAnyRole(['organizer', 'project_manager'])) {
        $links[] = ['Utenti', route('users.index'), request()->routeIs('users.*'), 'bi-people'];
        $links[] = ['Team',   route('teams.index'),  request()->routeIs('teams.*'),  'bi-diagram-3'];
    }
@endphp

<nav class="navbar navbar-expand-lg sticky-top bg-dark border-bottom border-primary border-opacity-50" data-bs-theme="dark" style="border-bottom-width:2px!important;">
    <div class="container-xl">
        {{-- Brand --}}
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ route('dashboard') }}">
            <x-application-mark />
            <span class="d-none d-sm-inline" style="letter-spacing:-.02em;">Flow<span class="text-primary">Desk</span></span>
        </a>

        {{-- Pulsante hamburger per dispositivi mobili --}}
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav"
                aria-controls="main-nav" aria-expanded="false" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="main-nav">
            {{-- Voci di menu principali (Dashboard, Progetti e condizionalmente Utenti/Team in base al ruolo) --}}
            <ul class="navbar-nav me-auto gap-1">
                @foreach ($links as [$label, $href, $active, $icon])
                    <li class="nav-item">
                        <a @class(['nav-link d-flex align-items-center gap-1 px-3', 'active' => $active])
                           href="{{ $href }}"
                           @if ($active) aria-current="page" @endif>
                            <i class="bi {{ $icon }} small"></i>{{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>

            {{-- User dropdown --}}
            <div class="dropdown">
                <button type="button"
                        class="btn btn-sm d-flex align-items-center gap-2 rounded-pill border border-white border-opacity-25 px-3 py-2"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    <x-avatar :user="$user" size="sm" />
                    <span class="d-none d-md-inline small fw-medium">{{ $user->full_name }}</span>
                    <i class="bi bi-chevron-down" style="font-size:.65rem;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg mt-2" data-bs-theme="light" style="min-width:220px;">
                    {{-- Header utente --}}
                    <li class="px-3 py-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <x-avatar :user="$user" size="lg" />
                            <div class="overflow-hidden">
                                <div class="fw-semibold text-truncate small">{{ $user->full_name }}</div>
                                <div class="text-body-secondary" style="font-size:.75rem;">{{ $user->email }}</div>
                                <div class="mt-1">
                                    @foreach($user->getRoleNames() as $role)
                                        <span class="badge text-bg-primary rounded-pill" style="font-size:.65rem;">{{ ucfirst(str_replace('_', ' ', $role)) }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </li>
                    <li><a class="dropdown-item py-2" href="{{ route('profile.show') }}">
                        <i class="bi bi-person-circle me-2 text-body-secondary"></i>Il mio profilo
                    </a></li>
                    @role('organizer')
                        <li><a class="dropdown-item py-2" href="{{ route('mail-settings.edit') }}">
                            <i class="bi bi-envelope-gear me-2 text-body-secondary"></i>Server di posta
                        </a></li>
                    @endrole
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item py-2 text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Esci
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

