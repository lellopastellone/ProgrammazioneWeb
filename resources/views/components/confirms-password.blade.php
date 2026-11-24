{{-- Involucro e finestra modale Livewire per la riconferma della password prima di azioni sensibili --}}
@props(['title' => 'Conferma password', 'content' => 'Per sicurezza, conferma la password per continuare.', 'button' => 'Conferma'])

{{-- Genera un identificativo univoco per distinguere quale pulsante ha richiesto la conferma --}}
@php
    $confirmableId = md5($attributes->wire('then'));
@endphp

<span
    {{ $attributes->wire('then') }}
    x-data
    x-ref="span"
    x-on:click="$wire.startConfirmingPassword('{{ $confirmableId }}')"
    x-on:password-confirmed.window="setTimeout(() => $event.detail.id === '{{ $confirmableId }}' && $refs.span.dispatchEvent(new CustomEvent('then', { bubbles: false })), 250);"
>
    {{ $slot }}
</span>

@once
<x-dialog-modal wire:model.live="confirmingPassword">
    <x-slot name="title">{{ $title }}</x-slot>

    <x-slot name="content">
        {{ $content }}

        <div class="mt-3" x-data="{}" x-on:confirming-password.window="setTimeout(() => $refs.confirmable_password.focus(), 250)">
            <x-input type="password" placeholder="Password" autocomplete="current-password"
                        x-ref="confirmable_password"
                        wire:model="confirmablePassword"
                        wire:keydown.enter="confirmPassword" />

            <x-input-error for="confirmable_password" />
        </div>
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="stopConfirmingPassword" wire:loading.attr="disabled">Annulla</x-secondary-button>
        <x-button dusk="confirm-password-button" wire:click="confirmPassword" wire:loading.attr="disabled">{{ $button }}</x-button>
    </x-slot>
</x-dialog-modal>
@endonce
