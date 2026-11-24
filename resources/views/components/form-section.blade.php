{{-- Layout a due colonne con modulo form per la modifica e il salvataggio dei dati --}}
@props(['submit'])

<div {{ $attributes->merge(['class' => 'row g-3']) }}>
    <x-section-title>
        <x-slot name="title">{{ $title }}</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>
    </x-section-title>

    <div class="col-md-8">
        <form wire:submit="{{ $submit }}" class="card shadow-sm border-0">
            <div class="card-body p-4"><div class="row g-3">{{ $form }}</div></div>

            @if (isset($actions))
                <div class="card-footer bg-light py-3 px-4 border-top d-flex justify-content-end align-items-center gap-3">{{ $actions }}</div>
            @endif
        </form>
    </div>
</div>

