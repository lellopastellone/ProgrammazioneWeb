{{-- Finestra modale con icona di pericolo per la conferma di azioni distruttive --}}
@props(['id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="modal-body p-4 d-flex gap-3">
        <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        </div>
        <div>
            <h5 class="modal-title fw-semibold text-dark mb-1">{{ $title }}</h5>
            <div class="small text-body-secondary">{{ $content }}</div>
        </div>
    </div>
    <div class="modal-footer border-top-0 pt-0 pb-4 px-4">{{ $footer }}</div>
</x-modal>
