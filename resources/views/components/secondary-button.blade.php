{{-- Pulsante secondario per azioni non primarie o per annullare un'operazione --}}
<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-light border']) }}>{{ $slot }}</button>
