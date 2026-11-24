{{-- Componente base universale per le finestre modali gestito con Alpine.js e Livewire --}}
@props(['id', 'maxWidth'])

@php
$id = $id ?? md5($attributes->wire('model'));
$size = ['sm' => 'modal-sm', 'xl' => 'modal-lg', '2xl' => 'modal-lg'][$maxWidth ?? 'md'] ?? '';
@endphp

{{-- Struttura grafica della modale Bootstrap, controllata in modo reattivo da Alpine.js e sincronizzata con Livewire (@entangle) anziché dal JS nativo di Bootstrap --}}
<div
    x-data="{ show: @entangle($attributes->wire('model')) }"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    id="{{ $id }}"
>
    @teleport('body')
        <div
            x-show="show"
            x-on:keydown.escape.window="show = false"
            style="display: none;"
        >
            <div class="modal-backdrop show" x-on:click="show = false"></div>
            <div class="modal d-block" tabindex="-1" x-on:click.self="show = false">
                <div class="modal-dialog modal-dialog-centered {{ $size }}" x-trap.inert.noscroll="show">
                    <div class="modal-content border-0 shadow-lg rounded-3">{{ $slot }}</div>
                </div>
            </div>
        </div>
    @endteleport
</div>
