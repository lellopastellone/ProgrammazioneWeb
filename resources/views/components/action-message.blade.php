{{-- Messaggio di feedback temporaneo a scomparsa automatica per azioni Livewire --}}
@props(['on'])

<div x-data="{ shown: false, timeout: null }"
    x-init="@this.on('{{ $on }}', () => { clearTimeout(timeout); shown = true; timeout = setTimeout(() => { shown = false }, 2000); })"
    x-show="shown"
    style="display: none;"
    {{ $attributes->merge(['class' => 'small text-body-secondary']) }}>
    {{ $slot->isEmpty() ? 'Salvato.' : $slot }}
</div>
