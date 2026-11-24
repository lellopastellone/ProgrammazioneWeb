{{-- Layout master per le pagine riservate agli utenti autenticati con navbar, modali e script --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    {{-- Metadati della pagina, font tipografici, asset compilati Vite e stili Livewire --}}
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#4F46E5">
        <meta name="description" content="FlowDesk — Gestione progetti, task e team per il tuo team.">

        <title>{{ isset($title) ? $title . ' · ' : '' }}{{ config('app.name', 'FlowDesk') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/scss/app.scss', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body>
        {{-- Banner globale per avvisi di sistema e barra di navigazione reattiva Livewire --}}
        <x-banner />

        @livewire('navigation-menu')

        {{-- Contenitore principale: avvisi di sessione (flash messages) e contenuto della vista ($slot) --}}
        <main class="container-xl py-4">
            @session('status')
                <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 fade show" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <div class="flex-grow-1">{{ $value }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
                </div>
            @endsession

            {{ $slot }}
        </main>

        @stack('modals')

        {{-- Modale globale Bootstrap per la conferma delle azioni distruttive (aperta dallo script app.js quando un pulsante richiede conferma prima di procedere) --}}
        <div class="modal fade" id="confirm-modal" tabindex="-1" aria-labelledby="confirm-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h2 class="modal-title h6 fw-semibold" id="confirm-modal-title">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Conferma
                        </h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                    </div>
                    <div class="modal-body text-body-secondary" data-confirm-message></div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Annulla</button>
                        <button type="button" class="btn btn-danger" data-confirm-ok>Conferma</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Script JavaScript: ombra dinamica sulla navbar allo scorrimento e attivazione dei tooltip Bootstrap --}}
        <script>
            // Navbar scroll shadow
            window.addEventListener('scroll', () => {
                document.querySelector('.navbar')?.classList.toggle('navbar-scrolled', window.scrollY > 4);
            }, { passive: true });
            // Tooltip Bootstrap
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                new bootstrap.Tooltip(el, { trigger: 'hover' });
            });
        </script>

        @livewireScripts
    </body>
</html>

