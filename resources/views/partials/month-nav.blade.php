{{-- Barra di navigazione temporale tra i mesi con pulsanti precedente e successivo --}}
<div class="d-flex align-items-center gap-2">
    <a class="btn btn-light btn-sm border" href="{{ $route($month->subMonth()->format('Y-m')) }}" aria-label="Mese precedente"><i class="bi bi-chevron-left"></i></a>
    <span class="h5 fw-semibold mb-0 text-capitalize text-center px-2">{{ $month->locale('it')->translatedFormat('F Y') }}</span>
    <a class="btn btn-light btn-sm border" href="{{ $route($month->addMonth()->format('Y-m')) }}" aria-label="Mese successivo"><i class="bi bi-chevron-right"></i></a>
</div>
