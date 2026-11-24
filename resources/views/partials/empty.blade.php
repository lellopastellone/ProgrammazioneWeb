{{-- Riquadro di stato vuoto per elenchi privi di dati. Parametri: $icon, $title, $text (opzionale), $cta (testo opzionale del pulsante di azione) e $ctaHref (link opzionale del pulsante di azione). --}}
<div class="card">
    <div class="card-body empty-state text-center">
        <div class="empty-icon mb-3">
            <i class="bi {{ $icon }}" style="font-size: 3.5rem;"></i>
        </div>
        <p class="fw-semibold mb-1 fs-6">{{ $title }}</p>
        @isset($text)
            <p class="small text-body-secondary mb-3">{{ $text }}</p>
        @endisset
        @isset($cta)
            <a href="{{ $ctaHref ?? '#' }}" class="btn btn-primary btn-sm">{{ $cta }}</a>
        @endisset
    </div>
</div>

