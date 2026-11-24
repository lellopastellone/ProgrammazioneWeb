{{-- Scheda statistica numerica con icona e barra superiore colorata per la dashboard --}}
@props(['label', 'value', 'icon', 'color' => 'primary', 'hint' => null])

<div class="card stat-card h-100 fade-in-up">
    <div class="card-body d-flex align-items-center gap-3 p-4">
        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:52px;height:52px;background:rgba(var(--bs-{{ $color }}-rgb),.12)">
            <i class="bi {{ $icon }} fs-4 text-{{ $color }}"></i>
        </div>
        <div class="min-w-0">
            <div class="display-6 fw-bold mb-0 lh-1">{{ $value }}</div>
            <div class="small text-body-secondary mt-1">{{ $label }}</div>
            @if ($hint)
                <div class="small text-body-secondary">{{ $hint }}</div>
            @endif
        </div>
    </div>
</div>

