{{-- Riquadro di riepilogo per gli errori di validazione generali restituiti dal form --}}
@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'alert alert-danger']) }}>
        <div class="fw-semibold"><i class="bi bi-exclamation-circle me-1"></i>Controlla i dati inseriti.</div>
        <ul class="mb-0 mt-2 small">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
