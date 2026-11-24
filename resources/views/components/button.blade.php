{{-- Pulsante primario standard (blu indaco) per l'invio dei form --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-primary']) }}>{{ $slot }}</button>
