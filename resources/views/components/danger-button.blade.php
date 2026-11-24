{{-- Pulsante di pericolo rosso per eliminazioni o azioni irreversibili --}}
<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-danger']) }}>{{ $slot }}</button>
