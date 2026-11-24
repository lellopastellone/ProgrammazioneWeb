{{-- Form di configurazione e modifica delle impostazioni del server di posta SMTP --}}
<x-page title="Configurazione Mail SMTP" subtitle="Impostazioni del server di posta utilizzato per inviare inviti e recupero password da FlowDesk.">
    <div class="row g-4">
        {{-- Colonna sinistra: form di aggiornamento parametri server SMTP --}}
        <div class="col-lg-8">
            <form method="POST" action="{{ route('mail-settings.update') }}" class="card shadow-sm border-0 overflow-hidden">
                @csrf
                @method('PUT')
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-envelope-at text-primary fs-5"></i>
                        <h2 class="h6 fw-semibold mb-0 text-dark">Parametri Server SMTP</h2>
                    </div>
                </div>
                <div class="card-body p-4 d-flex flex-column gap-3">
                    @include('partials.smtp-fields')
                </div>
                <div class="card-footer bg-body-tertiary border-top d-flex justify-content-end align-items-center gap-2 py-3 px-4">
                    <x-button class="btn-primary px-3">
                        <i class="bi bi-check2 me-1"></i> Salva configurazione
                    </x-button>
                </div>
            </form>
        </div>

        {{-- Colonna destra: riquadro informativo sui protocolli e sulle porte SMTP consigliate --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-2 text-primary">
                        <i class="bi bi-info-circle-fill"></i>
                        <h3 class="h6 fw-semibold mb-0">Informazioni e Porte</h3>
                    </div>
                    <p class="small text-secondary mb-3">
                        FlowDesk utilizza il protocollo SMTP per recapitare ai nuovi utenti il link per impostare la password e per il recupero password.
                    </p>
                    <ul class="list-unstyled small text-secondary d-flex flex-column gap-2 mb-0">
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-shield-check text-success mt-1"></i>
                            <div><strong>Porta 587:</strong> Consigliata per connessioni crittografate TLS/STARTTLS.</div>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-shield-lock text-info mt-1"></i>
                            <div><strong>Porta 465:</strong> Standard per connessioni dirette con crittografia SSL.</div>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-exclamation-triangle text-warning mt-1"></i>
                            <div><strong>Porta 25:</strong> Spesso bloccata o soggetta a filtri antispam dai provider.</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-page>
