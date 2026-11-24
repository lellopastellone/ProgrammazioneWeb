{{-- Etichetta descrittiva del campo nei form --}}
@props(['value'])

<label {{ $attributes->merge(['class' => 'form-label fw-medium']) }}>{{ $value ?? $slot }}</label>
