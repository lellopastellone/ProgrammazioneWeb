{{-- Campo di input generico con evidenziazione automatica degli errori di validazione --}}
@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($attributes->get('name', ''))]) }}>
