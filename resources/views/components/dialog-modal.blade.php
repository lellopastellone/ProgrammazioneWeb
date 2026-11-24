{{-- Finestra modale di dialogo generica a 3 sezioni (titolo, corpo, pulsanti) per Livewire --}}
@props(['id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="modal-header border-bottom-0 pb-0 pt-4 px-4"><h5 class="modal-title fw-semibold text-dark">{{ $title }}</h5></div>
    <div class="modal-body py-3 px-4 text-body-secondary">{{ $content }}</div>
    <div class="modal-footer border-top-0 pt-0 pb-4 px-4">{{ $footer }}</div>
</x-modal>
