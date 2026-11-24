{{-- Struttura standard delle pagine interne con breadcrumb, titolo, pulsanti d'azione e corpo --}}
@props(['title', 'subtitle' => null])

{{-- Guscio delle pagine: titolo, sottotitolo, azioni a destra (slot "actions"), breadcrumb opzionale, errori di validazione. --}}
<x-app-layout :title="$title">
    @isset($breadcrumb)
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-2">{{ $breadcrumb }}</ol></nav>
    @endisset

    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-semibold mb-1">{{ $title }}</h1>
            @if ($subtitle)
                <p class="text-body-secondary mb-0">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="d-flex flex-wrap gap-2">{{ $actions }}</div>
        @endisset
    </div>

    <x-validation-errors />

    {{ $slot }}
</x-app-layout>
