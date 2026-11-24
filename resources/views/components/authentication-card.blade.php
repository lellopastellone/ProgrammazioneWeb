{{-- Layout split-screen per le schermate di autenticazione con pannello del brand a sinistra e slot per il form a destra --}}
<div class="min-vh-100 d-flex flex-column flex-lg-row">
    {{-- Pannello laterale informativo con brand FlowDesk (visibile solo su schermi desktop grandi) --}}
    <div class="d-none d-lg-flex col-lg-5 col-xl-5 flex-column justify-content-between p-5 text-white position-relative"
         style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 60%, #312e81 100%);">
        
        {{-- Elementi grafici decorativi di sfondo sfocati --}}
        <div class="position-absolute rounded-circle" 
             style="width: 350px; height: 350px; background: rgba(99, 102, 241, 0.15); filter: blur(80px); top: -50px; left: -50px; pointer-events: none;"></div>
        <div class="position-absolute rounded-circle" 
             style="width: 300px; height: 300px; background: rgba(139, 92, 246, 0.12); filter: blur(70px); bottom: 20px; right: -20px; pointer-events: none;"></div>

        <div class="position-relative z-1">
            <a href="/" class="d-flex align-items-center gap-3 text-white text-decoration-none">
                <x-application-mark width="36" height="36" />
                <span class="fs-4 fw-bold tracking-tight">FlowDesk</span>
            </a>
        </div>

        <div class="position-relative z-1 my-auto py-5">
            <h1 class="display-6 fw-bold mb-3 text-white lh-sm">
                Lavora in armonia,<br>consegna in anticipo.
            </h1>
            <p class="text-white-50 lead fs-6 mb-4">
                La piattaforma unificata per coordinare team, tracciare backlog e raggiungere i tuoi traguardi senza attriti.
            </p>

            <div class="d-flex flex-column gap-3 pt-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-10 text-white" style="width: 36px; height: 36px;">
                        <i class="bi bi-kanban"></i>
                    </div>
                    <span class="text-white-75 small">Sprint, iterazioni e Kanban board reattiva</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-10 text-white" style="width: 36px; height: 36px;">
                        <i class="bi bi-people"></i>
                    </div>
                    <span class="text-white-75 small">Gestione carichi di lavoro e ruoli dedicati</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-10 text-white" style="width: 36px; height: 36px;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <span class="text-white-75 small">Accesso sicuro basato su permessi granulari</span>
                </div>
            </div>
        </div>

        <div class="position-relative z-1 text-white-50 small border-top border-white border-opacity-10 pt-3">
            &copy; {{ date('Y') }} FlowDesk. Tutti i diritti riservati.
        </div>
    </div>

    {{-- Contenitore destro: ospita la card con il form effettivo della pagina ($slot) --}}
    <div class="col-12 col-lg-7 col-xl-7 d-flex flex-column justify-content-center align-items-center p-4 p-sm-5 bg-body">
        {{-- Logo mostrato in alto solo su dispositivi mobili (dove il pannello sinistro è nascosto) --}}
        <div class="d-lg-none text-center mb-4">
            <a href="/" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                <x-application-mark width="32" height="32" />
                <span class="fs-4 fw-bold text-dark">FlowDesk</span>
            </a>
        </div>

        <div class="w-100" style="max-width: 520px;">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-sm-5">
                    {{ $slot }}
                </div>
            </div>

            @if (isset($footer))
                <p class="small text-body-secondary text-center mt-4 mb-0">
                    {{ $footer }}
                </p>
            @endif
        </div>
    </div>
</div>

