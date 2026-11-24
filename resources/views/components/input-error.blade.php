{{-- Messaggio testuale rosso per la visualizzazione degli errori di validazione del campo --}}
@props(['for'])

@error($for)
    <div {{ $attributes->merge(['class' => 'invalid-feedback d-block']) }}>{{ $message }}</div>
@enderror
