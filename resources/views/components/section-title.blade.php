{{-- Colonna sinistra con titolo e testo descrittivo per le sezioni di configurazione --}}
<div class="col-md-4">
    <h2 class="h6 fw-semibold mb-1">{{ $title }}</h2>
    <p class="small text-body-secondary mb-0">{{ $description }}</p>
    {{ $aside ?? '' }}
</div>
